<?php

namespace console\models\tasks;

/**
 * Interface TaskInterface
 * @package console\models\tasks
 */
interface TaskInterface
{

    /**
     * Executes task
     *
     * @return void
     */
    public function execute(): void;
}