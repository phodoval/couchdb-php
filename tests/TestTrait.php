<?php
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use Phodoval\CouchDB\Client;

trait TestTrait {
    public function getClient(array $responses): Client {
        $mock = new MockHandler($responses);

        $handlerStack = HandlerStack::create($mock);

        return new Client(
            host: 'http://hostname',
            port: 5984,
            user: 'user',
            password: 'pass',
            clientOptions: [
                'timeout' => 2,
                'handler' => $handlerStack,
            ],
        );
    }
}