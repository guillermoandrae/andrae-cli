<?php

namespace App\Db;

use App\Db\Helpers\QueryHelper;
use Aws\DynamoDb\DynamoDbClient;
use GuzzleHttp\Client;

class Adapter implements AdapterInterface
{
    /**
     * @var DynamoDbClient
     */
    private $dynamoDbClient;

    /**
     * @var Client
     */
    private $guzzleClient;

    private $dynamoDbDir = '';

    private $downloadPath = '';

    private $pidFilename = 'dynamodb.pid';

    private $pidPath = '';

    private $logFilename = 'dynamodb.log';

    private $logPath = '';

    /**
     * Adapter constructor.
     *
     * @param DynamoDbClient $dynamoDbClient The DynamoDB client.
     * @param Client $guzzleClient The Guzzle client.
     */
    public function __construct(DynamoDbClient $dynamoDbClient, Client $guzzleClient)
    {
        $this->dynamoDbClient = $dynamoDbClient;
        $this->guzzleClient = $guzzleClient;
        $this->dynamoDbDir = dirname(dirname(__DIR__)) . '/database/dynamodb';
        $this->downloadPath = sprintf('%s/%s', $this->dynamoDbDir, getenv('LOCAL_DYNAMODB_DOWNLOAD_FILENAME'));
        $this->pidPath = sprintf('%s/%s', $this->dynamoDbDir, $this->pidFilename);
        $this->logPath = sprintf('%s/%s', $this->dynamoDbDir, $this->logFilename);
    }

    public function installLocalDb()
    {
        $this->downloadLocalDb();
        $this->extractLocalDb();
    }

    public function startLocalDb()
    {
        if ($this->isLocalDbRunning()) {
            throw new \ErrorException('DynamoDB is already running!');
        }
        $port = parse_url(getenv('AWS_DYNAMODB_ENDPOINT'), PHP_URL_PORT);
        $cmd = sprintf(
            'nohup java "-Djava.library.path=%s/DynamoDBLocal_lib" -jar "%s/DynamoDBLocal.jar" -inMemory -port "%d"',
            $this->dynamoDbDir,
            $this->dynamoDbDir,
            $port
        );
        exec(sprintf("%s > %s 2>&1 & echo $! >> %s", $cmd, $this->logPath, $this->pidPath));
    }

    public function stopLocalDb()
    {
        if (!$this->isLocalDbRunning()) {
            throw new \ErrorException('DynamoDB is not running!');
        }
        exec(sprintf('kill -9 %d', $this->getPid()));
        if (file_exists($this->pidPath)) {
            unlink($this->pidPath);
        }
    }

    public function isLocalDbRunning(): bool
    {
        if (!$pid = $this->getPid()) {
            return false;
        }
        $result = shell_exec(sprintf("ps %d", $pid));
        if (count(preg_split("/\n/", $result)) > 2) {
            return true;
        }
        return false;
    }

    public function createTable(string $table, array $options)
    {
        if ($this->hasTable($table)) {
            throw new \ErrorException(sprintf("The '%s' table already exists.", $table));
        }
        $mergedOptions = [
            'TableName' => $table,
            'AttributeDefinitions' => [],
            'KeySchema' => [],
            'ProvisionedThroughput' => [
                'ReadCapacityUnits' => 5,
                'WriteCapacityUnits' => 5
            ]
        ];

        foreach ($options['columns'] as $column) {
            $mergedOptions['AttributeDefinitions'][] = [
                'AttributeName' => $column['field'],
                'AttributeType' => $column['type']
            ];
        }

        foreach ($options['keys'] as $key) {
            $mergedOptions['KeySchema'][] = [
                'AttributeName' => $key['field'],
                'KeyType' => $key['type']
            ];
        }

        $this->getDynamoDbClient()->createTable($mergedOptions);
        $this->getDynamoDbClient()->waitUntil('TableExists', [
            'TableName' => $table
        ]);
    }

    public function updateTable(string $table, array $options)
    {
        // TODO: Implement updateTable() method.
    }

    public function deleteTable(string $table)
    {
        $this->getDynamoDbClient()->deleteTable([
            'TableName' => $name
        ]);
        $this->getDynamoDbClient()->waitUntil('TableNotExists', [
            'TableName' => $name
        ]);
    }

    public function hasTable(string $table): bool
    {
        $tables = $this->listTables();
        return in_array($table, $tables);
    }

    public function describeTable(string $name): array
    {
        $data = [];
        $dynamoDbClient = $this->getDynamoDbClient();

        $result = $dynamoDbClient->describeTable(['TableName' => $name]);
        $indices = $result->toArray()['Table']['KeySchema'];

        $iterator = $dynamoDbClient->getIterator('Scan', [
            'TableName' => $name,
            'Limit' => 1
        ]);

        foreach ($iterator as $item) {
            foreach ($item as $field => $value) {
                $indexData = '';
                foreach ($indices as $index) {
                    if ($field == $index['AttributeName']) {
                        $indexData = $index['KeyType'];
                        break;
                    }
                }
                $data[] = [
                    'field' => $field,
                    'type' => array_keys($value)[0],
                    'index' => $indexData
                ];
            }
            break;
        }

        return $data;
    }

    public function listTables(): array
    {
        $result = $this->getDynamoDbClient()->listTables();
        return $result['TableNames'];
    }

    public function fetchItems(string $table, array $where = [], int $limit = AdapterInterface::FETCH_DEFAULT_LIMIT): array
    {
        $items = [];
        $options = ['TableName' => $table, 'Limit' => $limit];
        if ($where) {
            $options['ScanFilter'] = QueryHelper::where($where);
        }
        $iterator = $this->dynamoDbClient->getIterator('Scan', $options);

        // for some reason, limit is not being recognized in the query, so I'm slicing the array here
        $hardLimit = 0;
        foreach ($iterator as $item) {
            if ($hardLimit == $limit) {
                break;
            }
            $items[] = $this->flattenItem($item);
            $hardLimit++;
        }

        return $items;
    }

    public function insertItem(string $table, array $data): bool
    {
        $item = [];
        foreach ($data as $field => $value) {
            $item[$field] = ['S' => $value];
        }
        $result = $this->dynamoDbClient->putItem([
            'TableName' => $table,
            'Item' => $item
        ]);
        return isset($result['Item']);
    }

    public function updateItem(string $table, array $where, array $data): bool
    {
        $result = $this->dynamoDbClient->updateItem([
            'TableName' => $table
        ]);
    }

    public function deleteItem(string $table, array $where): bool
    {
        $result = $this->dynamoDbClient->deleteItem([
            'TableName' => $table,
            'Key' => [
                $field => ['S' => $value],
            ]
        ]);
    }

    public function getDynamoDbClient(): DynamoDbClient
    {
        return $this->dynamoDbClient;
    }

    public function getGuzzleClient(): Client
    {
        return $this->guzzleClient;
    }

    private function downloadLocalDb()
    {

        if (file_exists($this->downloadPath)) {
            throw new \ErrorException('DynamoDB already exists in the download directory.');
        }
        $client = new Client(['base_uri' => getenv('LOCAL_DYNAMODB_DOWNLOAD_BASE_URL')]);
        $client->request(
            'GET',
            '/' . getenv('LOCAL_DYNAMODB_DOWNLOAD_FILENAME'),
            ['sink' => $this->downloadPath]
        );
    }

    private function extractLocalDb()
    {
        if (file_exists(sprintf('%s/%s', $this->dynamoDbDir, 'README.txt'))) {
            throw new \ErrorException('DynamoDB was already extracted in the download directory.');
        }
        $phar = new \PharData(sprintf('%s/dynamodb_local_latest.tar.gz', $this->dynamoDbDir));
        $phar->extractTo($this->dynamoDbDir);
    }

    private function getPid()
    {
        if (file_exists($this->pidPath)) {
            return trim(file_get_contents($this->pidPath));
        }
    }

    private function flattenItem(array $item): array
    {
        $flattenedItem = [];
        foreach ($item as $field => $value) {
            $flattenedItem[$field] = array_pop($value);
        }
        return $flattenedItem;
    }
}
