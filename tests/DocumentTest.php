<?php

use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Phodoval\CouchDB\Document
 */
class DocumentTest extends TestCase {
    use TestTrait;

    public function testGetSuccessful() {
        $database = $this->getClient([
            new Response(200, [], '{"_id":"myDocId","_rev":"1-967a00dff5e02add41819138abb3284d","name":"John Doe"}'),
        ])->database('test');

        $document = $database->getDocument('myDocId');

        $this->assertNotNull($document);
        $this->assertEquals('myDocId', $document->getId());
        $this->assertEquals('1-967a00dff5e02add41819138abb3284d', $document->getRevision());
        $this->assertEquals('John Doe', $document->getData('name'));
    }

    public function testGetNotFound() {
        $database = $this->getClient([
            new Response(404, [], 'Not Found'),
        ])->database('test');

        $document = $database->getDocument('myDocId');

        $this->assertNull($document);
    }

    public function testCreateSuccessful() {
        $database = $this->getClient([
            new Response(200, [], '{"ok":true,"id":"myNewDoc","rev":"1-967a00dff5e02add41819138abb3284d"}'),
        ])->database('test');

        $document = $database->createDocument('myNewDoc', ['name' => 'John Doe']);
        $this->assertEquals('myNewDoc', $document->getId());
        $this->assertEquals('1-967a00dff5e02add41819138abb3284d', $document->getRevision());
        $this->assertEquals('John Doe', $document->getData('name'));
    }

    public function testCreateFailure(): void {
        $database = $this->getClient([
            new Response(409, [], '{"error":"conflict","reason":"Document update conflict."}'),
        ])->database('test');

        $this->expectException(GuzzleException::class);
        $database->createDocument('myNewDoc', ['name' => 'John Doe']);
    }

    public function testUpdateSuccess(): void {
        $database = $this->getClient([
            new Response(200, [], '{"_id":"myDocId","_rev":"1-967a00dff5e02add41819138abb3284d","name":"John Doe"}'),
            new Response(200, [], '{"ok":true,"id":"myDocId","rev":"2-7051cbe5c8faecd085a3fa619e6e6337"}'),
        ])->database('test');

        $document = $database->getDocument('myDocId');
        $this->assertEquals('1-967a00dff5e02add41819138abb3284d', $document->getRevision());
        $this->assertEquals('John Doe', $document->getData('name'));

        $document->setData(['name' => 'Jane Doe']);
        $document = $database->updateDocument($document);
        $this->assertEquals('2-7051cbe5c8faecd085a3fa619e6e6337', $document->getRevision());
        $this->assertEquals('Jane Doe', $document->getData('name'));
    }

    public function testUpdateFailure(): void {
        $database = $this->getClient([
            new Response(200, [], '{"_id":"myDocId","_rev":"1-967a00dff5e02add41819138abb3284d","name":"John Doe"}'),
            new Response(409, [], '{"error":"conflict","reason":"Document update conflict."}'),
        ])->database('test');

        $document = $database->getDocument('myDocId');
        $this->assertEquals('1-967a00dff5e02add41819138abb3284d', $document->getRevision());
        $this->assertEquals('John Doe', $document->getData('name'));

        $document->setData(['name' => 'Jane Doe']);

        $this->expectException(GuzzleException::class);
        $database->updateDocument($document);
    }

    public function testDeleteSuccess(): void {
        $database = $this->getClient([
            new Response(200, [], '{"_id":"myDocId","_rev":"1-967a00dff5e02add41819138abb3284d","name":"John Doe"}'),
            new Response(200, [], '{"ok":true,"id":"myDocId","rev":"2-7051cbe5c8faecd085a3fa619e6e6337"}'),
        ])->database('test');

        $document = $database->getDocument('myDocId');
        $result = $database->deleteDocument($document);

        $this->assertTrue($result);
    }

    public function testDeleteFailure(): void {
        $database = $this->getClient([
            new Response(200, [], '{"_id":"myDocId","_rev":"1-967a00dff5e02add41819138abb3284d","name":"John Doe"}'),
            new Response(409, [], '{"error":"conflict","reason":"Document update conflict."}'),
        ])->database('test');

        $document = $database->getDocument('myDocId');
        $result = $database->deleteDocument($document);

        $this->assertFalse($result);
    }
}