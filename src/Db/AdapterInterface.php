<?php

namespace App\Db;

use Aws\DynamoDb\DynamoDbClient;
use GuzzleHttp\Client;

interface AdapterInterface
{
    /**
     * The default limit used when fetching records from the database.
     *
     * @const integer
     */
    const FETCH_LIMIT = 25;

    /**
     * Installs DynamoDB locally.
     *
     * @return void
     */
    public function installLocalDb();

    /**
     * Starts DynamoDB locally.
     *
     * @return void
     */
    public function startLocalDb();

    /**
     * Stops DynamoDB locally.
     *
     * @return void
     */
    public function stopLocalDb();

    /**
     * Determines whether or not DynamoDB is running locally.
     *
     * @return bool
     */
    public function isLocalDbRunning(): bool;

    /**
     * Creates a new table.
     *
     * @param string $table The database table.
     * @param array $options  The database table options.
     * @return void
     */
    public function createTable(string $table, array $options);

    /**
     * Updates a table.
     *
     * @param string $table The database table.
     * @param array $options  The database table options.
     * @return void
     */
    public function updateTable(string $table, array $options);

    /**
     * Deletes a table.
     *
     * @param string $table The database table.
     * @return void
     */
    public function deleteTable(string $table);

    /**
     * Determines whether or not a table exists.
     *
     * @param string $table The database table.
     * @return bool
     */
    public function hasTable(string $table): bool;

    /**
     * Describes a table.
     *
     * @param string $table The database table.
     * @return array
     */
    public function describeTable(string $table): array;

    /**
     * Lists all of the tables.
     *
     * @return array
     */
    public function listTables(): array;

    /**
     * Retrieves records from the database.
     *
     * @param string $table  The database table.
     * @param array $where  The "where" conditions/filters.
     * @param int $limit  OPTIONAL The limit on the number of records to retrieve.
     * @return array
     */
    public function fetchItems(string $table, array $where = [], int $limit = AdapterInterface::FETCH_LIMIT): array;

    /**
     * Inserts records into the database.
     *
     * @param string $table  The database table.
     * @param array $data  The record data.
     * @return bool
     */
    public function insertItem(string $table, array $data): bool;

    /**
     * Updates a record in the database.
     *
     * @param string $table
     * @param array $where
     * @param array $data
     * @return bool
     */
    public function updateItem(string $table, array $where, array $data): bool;

    /**
     * @param string $table  The database table.
     * @param array $where  The "where" conditions/filters.
     * @return bool
     */
    public function deleteItem(string $table, array $where): bool;

    /**
     * Returns the registered DynamoDB client.
     *
     * @return DynamoDbClient
     */
    public function getDynamoDbClient(): DynamoDbClient;

    /**
     * Returns the Guzzle client.
     *
     * @return Client
     */
    public function getGuzzleClient(): Client;
}
