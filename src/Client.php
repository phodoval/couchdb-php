<?php
namespace Phodoval\CouchDB;

use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;

class Client {
    protected  \GuzzleHttp\Client $client;

    /**
     * @param string               $host
     * @param int                  $port
     * @param string               $user
     * @param string               $password
     * @param array<string, mixed> $clientOptions
     */
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
     * @param string                    $path
     * @param array<string, mixed>|null $data
     * @return ResponseInterface
     * @throws GuzzleException
     */
    public function put(string $path, array $data = null): ResponseInterface {
        return $this->client->request('PUT', $path, [
            'json' => $data,
        ]);
    }

    /**
     * @param string                    $path
     * @param array<string, mixed>|null $data
     * @return ResponseInterface
     * @throws GuzzleException
     */
    public function post(string $path, array $data = null): ResponseInterface {
        return $this->client->request('POST', $path, [
            'json' => $data,
        ]);
    }

    /**
     * @param string               $url
     * @param array<string, mixed> $options
     * @return ResponseInterface
     * @throws GuzzleException
     */
    public function delete(string $url, array $options = []): ResponseInterface {
        return $this->client->request('DELETE', $url, $options);
    }
}