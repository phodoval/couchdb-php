<?php
use Phodoval\CouchDB\Client;

trait TestTrait {
    protected Client $client;

    public function setUp(): void {
        $this->client = new Client(
            'http://host.docker.internal',
            5985,
            'admin',
            'admin'
        );
    }
}