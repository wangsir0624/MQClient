<?php
namespace Wangjian\MQClient;

use Wangjian\MQClient\Connection\Connection;

class Client {
    /**
     * the connection interface
     * @var Connection
     */
    protected $connection;

    /**
     * Client constructor.
     * @param string $ip
     * @param int $port
     * @param int $timeout
     */
    public function __construct($ip, $port, $timeout = 5) {
        $stream = stream_socket_client("tcp://$ip:$port", $errno, $errmsg, $timeout);
        if(!$stream) {
            throw new \RuntimeException("$errno: $errmsg");
        }

        $this->connection = new Connection($stream);
    }

    /**
     * create a queue
     * @param string $queue  the queue name
     * @param int $max_items  the max items in the queue
     * @return bool
     */
    public function createQueue($queue, $max_items = 10000) {
        $this->connection->send("new $queue $max_items");

        $respond = $this->connection->handleMessage();
        if($respond == 'created') {
            return true;
        } else {
            return false;
        }
    }

    /**
     * whether a queue exists
     * @param string $queue  the queue name
     * @return bool
     */
    public function existsQueue($queue) {
        $this->connection->send("exists $queue");

        $respond = $this->connection->handleMessage();
        if($respond == 'exists') {
            return true;
        } else {
            return false;
        }
    }

    /**
     * delete a queue
     * @param string $queue  the queue name
     * @return bool
     */
    public function deleteQueue($queue) {
        $this->connection->send("del $queue");

        $respond = $this->connection->handleMessage();
        if($respond == 'deleted') {
            return true;
        } else {
            return false;
        }
    }

    /**
     * add an item to the tail of a queue
     * @param string $queue  the queue name
     * @param mixed $item
     * @param bool $top  whether add the item at the top of the queue
     * @return bool
     */
    public function inQueue($queue, $item, $top = false) {
        if(is_array($item) || is_object($item)) {
            $item = serialize($item);
        }

        $this->connection->send("in $queue $item ".intval($top));

        $respond = $this->connection->handleMessage();
        if($respond == 'stored') {
            return true;
        } else {
            return false;
        }
    }

    /**
     * pop up an item from a queue
     * @return mixed
     */
    public function unQueue() {
        $queues = implode(' ', func_get_args());

        $this->connection->send("out $queues");

        $respond = $this->connection->handleMessage();
        if(substr($respond, 0, 5) == 'data ') {
            $item = substr($respond, 5);

            if(($us_item = @unserialize($item)) !== false) {
                $item = $us_item;
            }

            return $item;
        } else {
            return false;
        }
    }

    /**
     * close the connection
     */
    public function close() {
        $this->connection->close();
    }
}