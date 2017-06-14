<?php
use Wangjian\MQClient\Client;

require_once __DIR__.'/../vendor/autoload.php';

$client = new Client('127.0.0.1', 3000);
var_dump($client->unQueue('test', 'test11'));
