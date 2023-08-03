<?php
namespace Phodoval\CouchDB;

use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;

class Client {
    protected  \GuzzleHttp\Client $client;

    public function __construct(
        private string $host,
        private int $port,
        private string $user,
        private string $password,
        private array $clientOptions = [],
    ) {
        $this->client = new \GuzzleHttp\Client([
            'base_uri' => $this->host . ':' . $this->port,
            'auth' => [$this->user, $this->password],
        ] + $this->clientOptions);
    }

    public function database(string $name): Database {
        return new Database($this, $name);
    }

    /**
     * @param string $path
     * @return ResponseInterface
     * @throws GuzzleException
     */
    public function get(string $path): ResponseInterface {
        return $this->client->request('GET', $path);
    }

    /**
     * @throws GuzzleException
     */
    public function put(string $path, array $data = null): ResponseInterface {
        return $this->client->request('PUT', $path, [
            'json' => $data,
        ]);
    }

    /**
     * @throws GuzzleException
     */
    public function post(string $path, array $data = null): ResponseInterface {
        return $this->client->request('POST', $path, [
            'json' => $data,
        ]);
    }

    /**
     * @throws GuzzleException
     */
    public function delete(string $url, array $options = []): ResponseInterface {
        return $this->client->request('DELETE', $url, $options);
    }
}