<?php

use Phodoval\CouchDB\Client;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Phodoval\CouchDB\Database
 */
class DatabaseTest extends TestCase {
    use TestTrait;

    public function testPing(): void {
        $database = $this->client->database('sessions');
        $this->assertTrue($database->ping());
    }
}