<?php
require_once __DIR__.'/../vendor/autoload.php';

$client = new \Wangjian\MQClient\Client('127.0.0.1', 3000);

//创建一个名为test的队列
$client->createQueue('test');

//创建一个名为test1的队列，队列最大元素个数为1000
$client->createQueue('test1', 1000);

//检查队列是否存在
var_dump($client->existsQueue('test'));

//删除队列test1
$client->deleteQueue('test1');

//入队
$client->inQueue('test', 111);
$client->inQueue('test', [1, 2, 3]);
$client->inQueue('test', 222, true); //将222添加到队列头部

//出队
var_dump($client->unQueue('test'));

//设定队列的优先级
var_dump($client->unQueue('test1', 'test2', 'test3'));  //优先获取test1中的元素，如果test1为空，则获取test2的元素，依次类推，可以用这个特性来设定队列的优先级

