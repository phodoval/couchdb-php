<?php

use Phodoval\CouchDB\Document;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Phodoval\CouchDB\Document
 */
class DocumentTest extends TestCase {
    use TestTrait;

    public function testGet() {
        $database = $this->client->database('sessions');
        $document = new Document($database, 'n93flgmiiasn2plbcc1p136j39');
        $this->assertEquals('89uc0femskq2698hto7g8c30l4', $document->getId());
        $this->assertEquals(null, $document->getRevision());
        $document->load();
        $this->assertNotNull($document->getRevision());
        $this->assertNotNull($document->getData());
    }

    public function testSaveNew() {
        $document = new Document($this->client->database('sessions'), 'test');
        $response = $document->save();

        $this->assertEquals(201, $response->getStatusCode());
    }
}