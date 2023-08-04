<?php
namespace Phodoval\CouchDB;

class Document {
    /**
     * @param string               $id
     * @param string|null          $rev
     * @param array<string, mixed> $data
     */
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

    public function setRevision(string $rev): void {
        $this->rev = $rev;
    }

    /**
     * @return array<string, mixed>
     */
    public function getData(): array {
        return $this->data;
    }

    public function get(string $key): mixed {
        return $this->data[$key] ?? null;
    }

    /**
     * @param array<string, mixed> $data
     * @return void
     */
    public function setData(array $data): void {
        $this->data = array_merge($this->data, $data);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array {
        return [
            '_id' => $this->id,
            '_rev' => $this->rev,
        ] + $this->data;
    }
}