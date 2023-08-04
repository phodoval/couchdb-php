<?php
namespace Phodoval\CouchDB;

use CouchException;
use GuzzleHttp\Exception\GuzzleException;

class Database {
    public function __construct(
        private Client $client,
        private string $name,
    ) {}

    public function ping(): bool {
        try {
            return $this->client->head('/' . $this->name)->getStatusCode() === 200;
        } catch (GuzzleException) {
            return false;
        }
    }

    /**
     * @param string               $id
     * @param array<string, mixed> $data
     * @return Document
     * @throws GuzzleException
     * @throws CouchException
     */
    public function createDocument(string $id, array $data): Document {
        $requestData = $data;
        $requestData['_id'] = $id;

        $response = $this->client->post('/' . $this->name, $requestData);
        /**
         * @var array<string, string> $responseData
         */
        $responseData = json_decode($response->getBody()->getContents(), true);

        if (!is_array($responseData) || !isset($responseData['ok'])) {
            throw new CouchException('Failed to create document.');
        }

        return new Document($id, $responseData['rev'], $data);
    }

    public function getDocument(string $id): ?Document {
        try {
            /**
             * @var array<string, string> $data
             */
            $data = json_decode($this->client->get('/' . $this->name . '/' . $id)->getBody(), true);
        } catch (GuzzleException) {
            return null;
        }

        if (!is_array($data) || !isset($data['_rev'])) {
            return null;
        }

        $rev = $data['_rev'];
        unset($data['_rev']);

        return new Document($id, $rev, $data);
    }

    /**
     * @throws GuzzleException
     */
    public function updateDocument(Document $document): void {
        $requestData = $document->getData();
        $requestData['_rev'] = $document->getRevision();

        $response = $this->client->put('/' . $this->name . '/' . $document->getId(), $requestData);
        /**
         * @var array<string, string> $responseData
         */
        $responseData = json_decode($response->getBody()->getContents(), true);
        $document->setRevision($responseData['rev']);
    }

    public function deleteDocument(Document $document): bool {
        try {
            $this->client->delete('/' . $this->name . '/' . $document->getId(), [
                'query' => [
                    'rev' => $document->getRevision(),
                ],
            ]);
        } catch (GuzzleException) {
            return false;
        }

        return true;
    }
}