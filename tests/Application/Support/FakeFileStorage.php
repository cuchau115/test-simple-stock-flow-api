<?php

declare(strict_types=1);

namespace Tests\Application\Support;

use App\Application\Ports\Outbound\FileStorage;

final class FakeFileStorage implements FileStorage
{
    /** @var array<string, string> */
    private array $files = [];

    /** @var list<string> */
    public array $deleted = [];

    public function __construct(private readonly string $baseUrl = 'https://cdn.test/')
    {
    }

    public function store(string $contentType, string $binary): string
    {
        $key = 'img-'.substr(hash('sha256', $contentType.$binary), 0, 12);
        $this->files[$key] = $contentType;

        return $key;
    }

    public function delete(string $key): void
    {
        unset($this->files[$key]);
        $this->deleted[] = $key;
    }

    public function url(string $key): string
    {
        return $this->baseUrl.$key;
    }

    public function has(string $key): bool
    {
        return isset($this->files[$key]);
    }
}
