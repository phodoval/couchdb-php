<?php
namespace Phodoval\CouchDB;

use GuzzleHttp\Exception\GuzzleException;
use stdClass;

class Client {
    protected  \GuzzleHttp\Client $client;

    public function __construct(
        private string $host,
        private int $port,
        private string $user,
        private string $password
    ) {
        $this->client = new \GuzzleHttp\Client([
            'base_uri' => $this->host . ':' . $this->port,
            'auth' => [$this->user, $this->password],
            'timeout' => 2.0,
        ]);
    }

    public function database(string $name): Database {
        return new Database($this, $name);
    }

    /**
     * @param string $path
     * @return \Psr\Http\Message\ResponseInterface
     * @throws GuzzleException
     */
    public function get(string $path): \Psr\Http\Message\ResponseInterface {
        return $this->client->request('GET', $path);
    }

    public function put(string $path, ?stdClass $data = null): \Psr\Http\Message\ResponseInterface {
        return $this->client->request('PUT', $path, [
            'json' => $data,
        ]);
    }

    public function post(string $path, ?stdClass $data = null): \Psr\Http\Message\ResponseInterface {
        return $this->client->request('POST', $path, [
            'json' => $data,
        ]);
    }
}