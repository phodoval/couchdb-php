<?php
namespace Phodoval\CouchDB;

use GuzzleHttp\Exception\GuzzleException;

class Database {
    public function __construct(
        private Client $client,
        private string $name,
    ) {}

    public function getClient(): Client {
        return $this->client;
    }

    public function getName(): string {
        return $this->name;
    }

    public function ping(): bool {
        try {
            return $this->client->get('/' . $this->name)->getStatusCode() === 200;
        } catch (GuzzleException) {
            return false;
        }
    }
}