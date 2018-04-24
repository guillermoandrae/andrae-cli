#!/usr/bin/env php
<?php

require dirname(__DIR__) . '/vendor/autoload.php';

use Symfony\Component\Console\Application;
use App\Commands;

function dd($exp)
{
    var_dump($exp);
    exit;
}

$dotEnv = new Dotenv\Dotenv(dirname(__DIR__));
$dotEnv->load();

$adapter = new \App\Db\Adapter(
    new \Aws\DynamoDb\DynamoDbClient([
        'version' => getenv('AWS_SDK_VERSION') ?: 'latest',
        'region' => getenv('AWS_DYNAMODB_REGION'),
        'endpoint' => getenv('AWS_DYNAMODB_ENDPOINT'),
        'credentials' => [
            'key' => getenv('AWS_ACCESS_KEY_ID'),
            'secret' => getenv('AWS_SECRET_ACCESS_KEY')
        ]
    ]),
    new \GuzzleHttp\Client([
        'base_uri' => getenv('LOCAL_DYNAMODB_DOWNLOAD_BASE_URL')
    ])
);

$application = new Application('andrae');

$application->add(new Commands\ManageDatabaseCommand($adapter));
$application->add(new Commands\ManageTablesCommand($adapter));
$application->add(new Commands\ImportCommand($adapter));

$application->run();
