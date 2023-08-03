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
            return $this->client->get('/' . $this->name)->getStatusCode() === 200;
        } catch (GuzzleException) {
            return false;
        }
    }

    /**
     * @throws GuzzleException
     */
    public function createDocument(string $id, array $data): Document {
        $requestData = $data;
        $requestData['_id'] = $id;

        $response = $this->client->post('/' . $this->name, $requestData);
        $newData = json_decode($response->getBody()->getContents(), true);

        $rev = $newData['rev'];
        unset($newData['rev']);

        return new Document($id, $rev, $data);
    }

    public function getDocument(string $id): ?Document {
        try {
            $data = json_decode($this->client->get('/' . $this->name . '/' . $id)->getBody(), true);
        } catch (GuzzleException) {
            return null;
        }

        $rev = $data['_rev'];
        unset($data['_rev']);

        return new Document($id, $rev, $data);
    }

    /**
     * @throws GuzzleException
     */
    public function updateDocument(Document $document): Document {
        $data = $document->getData();
        $data['_rev'] = $document->getRevision();

        $response = $this->client->put('/' . $this->name . '/' . $document->getId(), $data);
        $newData = json_decode($response->getBody()->getContents(), true);

        $rev = $newData['rev'];
        unset($newData['rev']);

        unset($data['_rev']);

        return new Document($document->getId(), $rev, $data);
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