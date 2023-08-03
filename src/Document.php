<?php
namespace Phodoval\CouchDB;

class Document {
    public function __construct(
        private string $id,
        private ?string $rev = null,
        private array $data = [],
    ) {}

    public function getId(): string {
        return $this->id;
    }

    public function getRevision(): ?string {
        return $this->rev;
    }

    public function getData(string $key = null): mixed {
        if ($key) {
            return $this->data[$key] ?? null;
        }

        return $this->data;
    }

    public function setData(array $data): void {
        $this->data = array_merge($this->data, $data);
    }
}