<?php

namespace Tests\Db;

use Aws\Result;
use Aws\MockHandler as AwsMockHandler;
use Aws\DynamoDb\DynamoDbClient;
use Aws\CommandInterface;
use Psr\Http\Message\RequestInterface;
use Aws\Exception\AwsException;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Exception\RequestException;
use App\Db\Adapter;
use PHPUnit\Framework\TestCase;

class AdapterTest extends TestCase
{
    public function testConstructor()
    {
        $dynamoDbClient = $this->getMockDynamoDbClient();
        $guzzleClient = $this->getMockGuzzleClient();
        $adapter = new Adapter($dynamoDbClient, $guzzleClient);
        $this->assertInstanceOf(DynamoDbClient::class, $adapter->getDynamoDbClient());
        $this->assertInstanceOf(Client::class, $adapter->getGuzzleClient());
    }


    private function getMockDynamoDbClient(array $expectedResults = []): DynamoDbClient
    {
        $mock = new AwsMockHandler();

        foreach ($expectedResults as $expectedResult) {
            $mock->append(new Result($expectedResult));
        }

        return new DynamoDbClient([
            'region'  => 'us-west-2',
            'version' => 'latest',
            'handler' => $mock
        ]);
    }

    private function getMockGuzzleClient(): Client
    {
        $mock = new MockHandler([
            new Response(200, ['X-Foo' => 'Bar']),
            new Response(202, ['Content-Length' => 0]),
            new RequestException("Error Communicating with Server", new Request('GET', 'test'))
        ]);

        $handler = HandlerStack::create($mock);
        return new Client(['handler' => $handler]);
    }
}