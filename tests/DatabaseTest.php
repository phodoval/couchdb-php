<?php
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Phodoval\CouchDB\Database
 */
class DatabaseTest extends TestCase {
    use TestTrait;

    public function testPingSuccessful(): void {
        $database = $this->getClient([
            new Response(200, [], 'Hello, World'),
        ])->database('test');

        $this->assertTrue($database->ping());
    }

    public function testPingFailure(): void {
        $database = $this->getClient([
            new Response(404, [], 'Not Found'),
        ])->database('test');

        $this->assertFalse($database->ping());
    }
}