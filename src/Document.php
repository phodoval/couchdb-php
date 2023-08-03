<?php
namespace Phodoval\CouchDB;

use GuzzleHttp\Exception\GuzzleException;
use stdClass;

class Document {
    protected ?stdClass $data = null;
    protected ?string $rev = null;

    public function __construct(
        private Database $database,
        private string $id,
    ) {}

    public function getId(): string {
        return $this->id;
    }

    public function getRevision(): ?string {
        return $this->rev;
    }

    /**
     * @throws GuzzleException
     */
    public function load(): void {
        $data = json_decode($this->database->getClient()->get('/' . $this->database->getName() . '/' . $this->id)->getBody());
        $this->data = new stdClass();
        foreach ($data as $key => $value) {
            if (strpos($key, '_') === 0) {
                $replacedKey = substr($key, 1);
                $this->$replacedKey = $value;
            } else {
                $this->data->$key = $value;
            }
        }
    }

    public function getData(): ?stdClass {
        return $this->data;
    }

    public function save(): \Psr\Http\Message\ResponseInterface {
        $data = $this->data ?? new stdClass();

        if ($this->rev) {
            $data->_rev = $this->rev;
            $response = $this->database->getClient()->put('/' . $this->database->getName() . '/' . $this->id, $data);
        } else {
            $data->_id = $this->id;
            $response = $this->database->getClient()->post('/' . $this->database->getName(), $data);
            if ($response->getStatusCode() === 201) {
                $this->rev = json_decode($response->getBody())->rev;
            }
        }
        $this->rev = json_decode($response->getBody())->rev;

        return $response;
    }
}