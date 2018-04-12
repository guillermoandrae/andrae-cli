<?php

namespace App\Db;

use Aws\DynamoDb\DynamoDbClient as AWSDynamoDBClient;

class DynamoDBClient implements ClientInterface
{
    /**
     * @var AWSDynamoDBClient
     */
    private $client;

    public function __construct(AWSDynamoDBClient $client)
    {
        $this->client = $client;
    }

    public function insert($table, array $data): bool
    {
        $item = [];
        foreach ($data as $field => $value) {
            $item[$field] = ['S' => $value];
        }
        $result = $this->client->putItem([
            'TableName' => $table,
            'Item' => $item
        ]);
        return isset($result['Item']);
    }

    public function update($table, $field, $value, array $data): array
    {
        // TODO: Implement update() method.
    }

    public function delete($table, $field, $value): bool
    {
        $result = $this->client->deleteItem([
            'TableName' => $table,
            'Key' => [
                $field => ['S' => $value],
            ]
        ]);
    }

    public function selectOne($table, $field, $value): array
    {
        return $this->select($table, $field, $value, 1);
    }

    public function select($table, $limit): array
    {
        $items = ['headers' => [], 'rows' => []];
        $iterator = $this->client->getIterator('Scan', [
            'TableName' => $table,
            'Limit' => $limit
        ]);
        foreach ($iterator as $item) {
            $items['headers'] = array_keys($item);
            $row = [];
            foreach ($item as $i) {
                $length = 20;
                if (strlen($i['S']) > $length) {
                    $i['S'] = substr_replace($i['S'], '...', $length);
                }
                $row[] = $i['S'];
            }
            $items['rows'][] = $row;
        }

        return $items;
    }

    public function selectWhere($table, $field, $value, $limit): array
    {
        $items = [];
        $iterator = $this->client->getIterator('Scan', [
            'TableName' => $table,
            'ScanFilter' => [
                $field => [
                    'AttributeValueList' => [
                        ['S' => $value]
                    ],
                    'ComparisonOperator' => 'EQ'
                ]
            ]
        ]);
        foreach ($iterator as $item) {
            $items[] = $this->flattenItem($item);
        }
        return $items;
    }

    public function showTables(): array
    {
        $result = $this->client->listTables();
        return $result['TableNames'];
    }

    public function describeTable($table): array
    {
        $info = [
            'headers' => ['Field', 'Value'],
            'rows' => []
        ];
        $result = $this->client->describeTable(['TableName' => $table]);
        $tableInfo = $result->toArray()['Table'];
        $throughput = $tableInfo['ProvisionedThroughput'];
        $creationDateTime = (array) $tableInfo['CreationDateTime'];
        $info['rows'][] = ['TableName', $tableInfo['TableName']];
        $info['rows'][] = ['TableStatus', $tableInfo['TableStatus']];
        $info['rows'][] = [
            'CreationDateTime',
            sprintf(
                '%s %s',
                $creationDateTime['date'],
                $creationDateTime['timezone']
            )
        ];
        $info['rows'][] = ['ReadCapacityUnits', $throughput['ReadCapacityUnits']];
        $info['rows'][] = ['WriteCapacityUnits', $throughput['WriteCapacityUnits']];
        return $info;
    }

    private function flattenItem(array $item)
    {
        $flattenedItem = [];
        foreach ($item as $field => $value) {
            $flattenedItem[$field] = $value['S'];
        }
    }
}
