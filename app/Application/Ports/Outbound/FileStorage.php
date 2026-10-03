<?php

declare(strict_types=1);

namespace App\Application\Ports\Outbound;

/**
 * Opaque binary storage. Keys and URLs cross the port; paths and bytes never
 * reach the domain (RNF-06).
 */
interface FileStorage
{
    public function store(string $contentType, string $binary): string;

    public function delete(string $key): void;

    public function url(string $key): string;
}
