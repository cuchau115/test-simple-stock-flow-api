<?php

declare(strict_types=1);

namespace App\Application\Ports\Inbound;

final class PlaceSaleCommand
{
    /**
     * @param  list<PlaceSaleLine>  $lines
     */
    public function __construct(
        public readonly array $lines,
    ) {
    }

    /**
     * @param  list<array{productId: string, quantity: int}>  $lines
     */
    public static function fromArray(array $lines): self
    {
        return new self(array_map(
            static fn (array $line): PlaceSaleLine => new PlaceSaleLine($line['productId'], $line['quantity']),
            $lines,
        ));
    }
}
