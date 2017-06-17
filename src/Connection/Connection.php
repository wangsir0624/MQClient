<?php
namespace Wangjian\MQClient\Connection;

use Wangjian\MQClient\Protocol\MQServerProtocol;

class Connection implements ConnectionInterface {
    /**
     * the socket stream
     * @var resource
     */
    protected $stream;

    /**
     * the receive buffer
     * @var string
     */
    protected $recv_buffer = '';

    /**
     * the receive buffer size
     * @var int
     */
    protected $recv_buffer_size = 1048576;

    /**
     * Connection constructor.
     * @param resource $stream
     */
    public function __construct($stream) {
        $this->stream = $stream;
        stream_set_read_buffer($this->stream, $this->recv_buffer_size);
    }

    /**
     * send buffer
     * @param string $buffer
     * @param bool $raw  whether encode the buffer with the protocol
     * @return int
     */
    public function send($buffer, $raw = false) {
        if($buffer) {
            if(!$raw) {
                $buffer = MQServerProtocol::encode($buffer, $this);
            }

            $len = strlen($buffer);
            $writeLen = 0;
            while(($bytes = fwrite($this->stream, substr($buffer, $writeLen), $len-$writeLen)) != false) {
                $writeLen += $bytes;
                if($writeLen >= $len) {
                    return $len;
                }
            }
        }

        return 0;
    }

    /**
     * handle message
     * @return string
     */
    public function handleMessage() {
        $this->recv_buffer .= fread($this->stream, $this->recv_buffer_size);
        $package_size = MQServerProtocol::input($this->recv_buffer, $this);

        if($package_size != 0) {
            $buffer = substr($this->recv_buffer, 0, $package_size);
            $this->recv_buffer = substr($this->recv_buffer, $package_size);
            return MQServerProtocol::decode($buffer, $this);
        } else {
            return $this->handleMessage();
        }
    }

    /**
     * close the connection
     */
    public function close() {
        fclose($this->stream);
    }

    /**
     * get the client address, including IP and port
     * @return string
     */
    public function getRemoteAddress() {
        return stream_socket_get_name($this->stream, true);
    }
    /**
     * get the client IP
     * @return string
     */
    public function getRemoteIp() {
        return substr($this->getRemoteAddress(), 0, strpos($this->getRemoteAddress(), ':'));
    }
    /**
     * get the client port
     * @return string
     */
    public function getRemotePort() {
        return substr($this->getRemoteAddress(), strpos($this->getRemoteAddress(), ':')+1);
    }
}