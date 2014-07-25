<?php namespace Devitek\Net\Torrent;

use Closure;
use Devitek\Net\Torrent\Client\Client;
use Devitek\Net\Torrent\Exception\SendFailException;

/**
 * Class Seeder
 * @package Devitek\Net\Torrent
 */
class Seeder
{
    /**
     * @var array
     */
    private $events = [];

    /**
     * @var Client
     */
    protected $client;

    /**
     * @var Torrent
     */
    protected $torrent;

    /**
     * @var int
     */
    protected $port;

    /**
     * @var int
     */
    protected $refresh;

    /**
     * @var int
     */
    protected $speed;

    /**
     * @var
     */
    protected $uploaded;

    /**
     * @var mixed
     */
    protected $connection = null;

    /**
     * @param Client  $client
     * @param Torrent $torrent
     * @param int     $speed
     * @param int     $refresh
     */
    function __construct(Client $client, Torrent $torrent, $speed = 1, $refresh = 5)
    {
        $this->client  = $client;
        $this->torrent = $torrent;
        $this->speed   = $speed;
        $this->refresh = $refresh;

        $this->port = rand(30000, 60000);
    }

    /**
     * Seed the torrent
     */
    public function seed()
    {
        $this->uploaded = 0;
        $time           = time();

        try {
            $this->start();
        } catch (SendFailException $sfe) {
            $this->trigger('error', [
                'exception' => $sfe,
            ]);
        }

        while (true) {
            sleep($this->refresh);

            $now         = time();
            $randomSpeed = $this->getRandomSpeed();

            $this->uploaded += (($now - $time) * $randomSpeed * 1024 * 1024);

            $time = $now;
            $url  = $this->getUrl($this->uploaded, null);

            try {
                $this->send($url);
                $this->trigger('update', [
                    'uploaded' => ($this->uploaded / 1024 / 1024),
                    'speed'    => $randomSpeed,
                ]);
            } catch (SendFailException $sfe) {
                $this->trigger('error', [
                    'exception' => $sfe,
                ]);
            }
        }
    }

    /**
     * Return a random speed based on speed that the user provide
     * It returns a float between speed - 15% and speed + 10%
     * @return float
     */
    protected function getRandomSpeed()
    {
        return (rand(($this->speed * 100 * 0.85), ($this->speed * 100 * 1.10)) / 100);
    }

    /**
     * Send a request to the tracker and return an exception if it fails
     *
     * @param $url
     *
     * @throws Exception\SendFailException
     */
    protected function send($url)
    {
        $connection = $this->connection();

        curl_setopt($connection, CURLOPT_URL, $url);
        curl_exec($connection);

        if (!is_int(curl_getinfo($this->connection, CURLINFO_HTTP_CODE)) || 200 != curl_getinfo($this->connection, CURLINFO_HTTP_CODE)) {
            throw new SendFailException('Fail sending last query, return code is : ' . curl_getinfo($this->connection, CURLINFO_HTTP_CODE));
        }
    }

    /**
     * Send the "started" query
     */
    protected function start()
    {
        $url = $this->getUrl();

        $this->send($url);
    }

    /**
     * Return a formatted url
     *
     * @param int    $uploaded
     * @param string $event
     *
     * @return string
     */
    protected function getUrl($uploaded = 0, $event = 'started')
    {
        return $this->torrent->announce()
        . '?info_hash=' . urlencode(pack('H*', $this->torrent->hash_info()))
        . '&peer_id=' . $this->client->getPeerId()
        . '&key=' . $this->client->getKey()
        . '&supportcrypto=1'
        . '&port=' . $this->port
        . '&azudp=' . ($this->port + 1)
        . '&uploaded=' . $uploaded
        . '&downloaded=0'
        . '&left=0'
        . '&corrupt=0'
        . '&numwant=0'
        . '&no_peer_id=1'
        . '&compact=1'
        . '&azver=3'
        . (null != $event ? '&event=' . $event : '');
    }

    /**
     * Return a new connection
     * @return resource
     */
    protected function connection()
    {
        $this->connection = curl_init();

        curl_setopt_array($this->connection, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_USERAGENT      => $this->client->getUserAgent(),
            CURLINFO_HEADER_OUT    => true,
        ]);

        return $this->connection;
    }

    /**
     * Fire an event
     *
     * @param string $event the event name
     * @param array  $args  the event args
     */
    protected function trigger($event, $args = [])
    {
        if (isset($this->events[$event])) {
            foreach ($this->events[$event] as $func) {
                call_user_func($func, $args);
            }
        }
    }


    /**
     * Subscribe to an event
     *
     * @param  string  $event the event name
     * @param callable $func  a closure
     */
    public function bind($event, Closure $func)
    {
        $this->events[$event][] = $func;
    }
}