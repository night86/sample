<?php

namespace Module;

use Codeception\Configuration;
use Codeception\Exception\ModuleException;
use Codeception\Lib\Driver\MongoDb as MongoDbDriver;
use Codeception\Module\MongoDb;
use Codeception\Util\Debug;
use Exception;
use MongoConnectionException;
use MongoDB\Client;
use MongoDB\Database;
use PHPUnit_Framework_Assert;

/**
 * Class MongoDbModule
 * @package Module
 */
class MongoDbModule extends MongoDb
{
    /**
     * MongoDB database object
     *
     * @var Database
     */
    public $dbh;

    /**
     * MongoDB client to communicate with the database
     *
     * @var Client
     */
    protected Client $client;

    /**
     * Collections, which should be populated only once before the suite is loaded.
     *
     * @var array
     */
    protected array $populateOnce = [
        'rbac_auth_assignment',
        'rbac_auth_item',
        'rbac_auth_item_child',
        'rbac_auth_rule'
    ];

    /**
     * Flag to wipe the db clean upon new execution
     *
     * @var bool
     */
    private bool $firstCleanup = true;

    /**
     * @inheritdoc
     * @throws ModuleException
     * @throws Exception
     */
    public function _initialize(): void
    {
        try {
            $this->driver = MongoDbDriver::create(
                $this->config['dsn'],
                $this->config['user'],
                $this->config['password']
            );

            $this->client = new Client($this->config['dsn']);
            $dbName = preg_replace('/\?.*/', '', substr($this->config['dsn'], strrpos($this->config['dsn'], '/') + 1));
            $this->dbh = $this->client->selectDatabase($dbName);
        } catch (MongoConnectionException $e) {
            throw new ModuleException(__CLASS__, $e->getMessage() . ' while creating Mongo connection');
        }

        // starting with loading dump
        if ($this->config['populate']) {
            $this->cleanup();
            $this->loadDump();
            $this->loadDump($this->config['populate_once']);
            $this->populated = true;
        }
    }

    /**
     * Function to clean up the database
     *
     * @throws Exception
     */
    protected function cleanup(): void
    {
        try {
            $collections = $this->dbh->listCollections();
            foreach ($collections as $collection) {
                $collectionName = $collection->getName();
                if (!$this->firstCleanup && in_array($collectionName, $this->populateOnce, true)) {
                    continue;
                }

                $this->dbh->dropCollection($collectionName);
            }
        } catch (Exception $e) {
            throw new Exception(sprintf('Failed to drop the DB: %s', $e->getMessage()));
        }
        $this->firstCleanup = false;
    }

    /**
     * Function that dumps the data into the database from the js file
     *
     * @param ?string $dumpFile
     * @throws ModuleException
     */
    protected function loadDump(string $dumpFile = null): void
    {
        if ($dumpFile !== null) {
            $this->driver->load($dumpFile);
            return;
        }

        if (isset($this->config['shell_dns'], $this->config['shell_options'])) {
            $cmd = sprintf(
                'mongo "%s" %s %s',
                $this->config['shell_dns'],
                $this->config['shell_options'],
                escapeshellarg(Configuration::projectDir() . $this->config['dump'])
            );
            $result = shell_exec($cmd);

            Debug::debug($result);
        } else {
            parent::loadDump();
        }
    }

    /**
     * Database setter
     *
     * @param string $dbName
     * @return void
     */
    public function useDatabase($dbName): void
    {
        $this->client->selectDatabase($dbName);
    }

    /**
     * @inheritdoc
     */
    public function haveInCollection($collection, array $data)
    {
        $collection = $this->dbh->selectCollection($collection);
        if ($this->driver->isLegacy()) {
            $collection->insert($data);
            return $data['_id'];
        }

        $response = $collection->insertOne($data);
        return $response->getInsertedId()->__toString();
    }

    /**
     * @inheritdoc
     */
    public function seeInCollection($collection, $criteria = []): void
    {
        $collection = $this->dbh->selectCollection($collection);
        $res = $collection->count($criteria);
        PHPUnit_Framework_Assert::assertGreaterThan(0, $res);
    }

    /**
     * @inheritdoc
     */
    public function dontSeeInCollection($collection, $criteria = []): void
    {
        $collection = $this->dbh->selectCollection($collection);
        $res = $collection->count($criteria);
        PHPUnit_Framework_Assert::assertLessThan(1, $res);
    }

    /**
     * @inheritdoc
     */
    public function grabFromCollection($collection, $criteria = [])
    {
        $collection = $this->dbh->selectCollection($collection);
        return $collection->findOne($criteria);
    }

    public function grabAllFromCollection($collection, $criteria = []): array
    {
        $collection = $this->dbh->selectCollection($collection);
        return $collection->find($criteria)->toArray();
    }

    /**
     * @inheritdoc
     */
    public function grabCollectionCount($collection, $criteria = [])
    {
        $collection = $this->dbh->selectCollection($collection);
        return $collection->count($criteria);
    }

    /**
     * @inheritdoc
     */
    public function seeElementIsArray($collection, $criteria = [], $elementToCheck = null)
    {
        $collection = $this->dbh->selectCollection($collection);

        $res = $collection->count(
            array_merge(
                $criteria,
                [
                    $elementToCheck => ['$exists' => true],
                    '$where' => "Array.isArray(this.{$elementToCheck})"
                ]
            )
        );
        if ($res > 1) {
            throw new \PHPUnit_Framework_ExpectationFailedException(
                'Error: you should test against a single element criteria when asserting that elementIsArray'
            );
        }
        PHPUnit_Framework_Assert::assertEquals(1, $res, 'Specified element is not a Mongo Object');
    }

    /**
     * @inheritdoc
     */
    public function seeElementIsObject($collection, $criteria = [], $elementToCheck = null): void
    {
        $collection = $this->dbh->selectCollection($collection);

        $res = $collection->count(
            array_merge(
                $criteria,
                [
                    $elementToCheck => ['$exists' => true],
                    '$where' => "! Array.isArray(this.{$elementToCheck}) && isObject(this.{$elementToCheck})"
                ]
            )
        );
        if ($res > 1) {
            throw new \PHPUnit_Framework_ExpectationFailedException(
                'Error: you should test against a single element criteria when asserting that elementIsObject'
            );
        }
        PHPUnit_Framework_Assert::assertEquals(1, $res, 'Specified element is not a Mongo Object');
    }

    /**
     * @inheritdoc
     */
    public function seeNumElementsInCollection($collection, $expected, $criteria = []): void
    {
        $collection = $this->dbh->selectCollection($collection);
        $res = $collection->count($criteria);
        PHPUnit_Framework_Assert::assertSame($expected, $res);
    }
}
