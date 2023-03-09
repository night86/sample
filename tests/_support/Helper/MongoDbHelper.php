<?php

namespace Helper;

use Codeception\Exception\ModuleException;
use Codeception\Module;
use Module\MongoDbModule;
use MongoDB\Collection;
use MongoDB\InsertOneResult;

/**
 * Class MongoDbHelper
 * Helper for missing MongoDb methods
 *
 * @package Helper
 * @property MongoDbModule $mongoModule
 */
class MongoDbHelper extends Module
{
    /**
     * @var MongoDbModule
     */
    protected $mongoModule;

    /**
     * @inheritdoc
     * @throws ModuleException
     */
    public function _initialize(): void
    {
        $this->mongoModule = $this->getModule(MongoDbModule::class);
    }

    /**
     * Insert one document into collection
     *
     * @param string $collection
     * @param array $data
     * @return InsertOneResult
     */
    public function insertOneInCollection(string $collection, array $data): InsertOneResult
    {
        /** @var Collection $collection */
        $collection = $this->mongoModule->dbh->selectCollection($collection);

        return $collection->insertOne($data);
    }

    /**
     * Update collection data
     *
     * @param string $collection
     * @param array $data
     * @param array $criteria
     * @return void
     */
    public function updateCollection(string $collection, array $data, array $criteria): void
    {
        /** @var Collection $collection */
        $collection = $this->mongoModule->dbh->selectCollection($collection);
        $collection->updateOne($criteria, ['$set' => $data]);
    }

    /**
     * Update collection data
     *
     * @param string $collection
     * @param array $data
     * @param array $criteria
     * @return void
     */
    public function updateCollections(string $collection, array $data, array $criteria): void
    {
        /** @var Collection $collection */
        $collection = $this->mongoModule->dbh->selectCollection($collection);
        $collection->updateMany($criteria, ['$set' => $data]);
    }

    /**
     * Drop single collection
     *
     * @param string $collection
     * @return void
     */
    public function deleteCollection(string $collection): void
    {
        /** @var Collection $collection */
        $collection = $this->mongoModule->dbh->selectCollection($collection);
        $collection->drop();
    }

    /**
     * Mongodb FindAll
     *
     * @param string $collection
     * @param array $criteria
     * @return array
     */
    public function getCollections(string $collection, array $criteria = []): array
    {
        /** @var Collection $collection */
        $collection = $this->mongoModule->dbh->selectCollection($collection);
        $documents = [];
        foreach ($collection->find($criteria) as $doc) {
            $documents[] = (array)$doc;
        }

        return $documents;
    }
}
