<?php namespace Devitek\Net\Torrent\Client;

/**
 * Class Client
 * @package Devitek\Net\Torrent\Client
 */
class Client
{
    /**
     * The UserAgent string for the client
     *
     * @var string $userAgent
     */
    public $userAgent;

    /**
     * The client key
     *
     * @var string $key
     */
    public $key;

    /**
     * The client peer ID
     *
     * @var string $peerId
     */
    public $peerId;

    /**
     * @param $key
     * @param $peerId
     * @param $userAgent
     */
    function __construct($key, $peerId, $userAgent)
    {
        $this->key       = $key;
        $this->peerId    = $peerId;
        $this->userAgent = $userAgent;
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @param string $key
     */
    public function setKey($key)
    {
        $this->key = $key;
    }

    /**
     * @return string
     */
    public function getPeerId()
    {
        return $this->peerId;
    }

    /**
     * @param string $peerId
     */
    public function setPeerId($peerId)
    {
        $this->peerId = $peerId;
    }

    /**
     * @return string
     */
    public function getUserAgent()
    {
        return $this->userAgent;
    }

    /**
     * @param string $userAgent
     */
    public function setUserAgent($userAgent)
    {
        $this->userAgent = $userAgent;
    }
} 