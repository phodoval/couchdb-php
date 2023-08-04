<?php
namespace Phodoval\CouchDB;

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

    /**
     * @param Document[] $documents
     * @return int Number of updated rows
     * @throws GuzzleException
     */
    public function updateDocuments(array $documents): int {
        $requestData = [
            'docs' => array_map(fn (Document $document) => $document->toArray(), $documents),
        ];

        $response = $this->client->post('/' . $this->name . '/_bulk_docs', $requestData);

        /**
         * @var array<array{id: string, rev: string, ok: bool}> $responseData
         */
        $responseData = json_decode($response->getBody()->getContents(), true);

        $affectedRows = 0;
        foreach ($responseData as $index => $document) {
            if ($document['ok']) {
                $documents[$index]->setRevision($document['rev']);
                $affectedRows++;
            }
        }

        return $affectedRows;
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

    /**
     * @param Document[] $documents
     * @return int Number of deleted rows
     * @throws GuzzleException
     */
    public function deleteDocuments(array $documents): int {
        foreach ($documents as $document) {
            $document->setData(['_deleted' => true]);
        }

        return $this->updateDocuments($documents);
    }

    /**
     * @param array<string, mixed> $query
     * @throws GuzzleException
     */
    public function deleteDocumentsByQuery(array $query): int {
        $documents = $this->findDocuments($query);
        return $this->deleteDocuments($documents);
    }

    /**
     * @param array<string, mixed> $query
     * @return Document[]
     * @throws GuzzleException
     */
    public function findDocuments(array $query): array {
        $response = $this->client->post('/' . $this->name . '/_find', $query);
        /**
         * @var array{docs: array<array{_id: string, _rev: string}>} $responseData
         */
        $responseData = json_decode($response->getBody()->getContents(), true);
        $documents = [];

        foreach ($responseData['docs'] as $document) {
            $id = $document['_id'];
            $rev = $document['_rev'];
            unset($document['_id'], $document['_rev']);
            $documents[] = new Document($id, $rev, $document);
        }

        return $documents;
    }
}