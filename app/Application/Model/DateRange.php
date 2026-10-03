<?php

declare(strict_types=1);

namespace App\Application\Model;

use App\Application\Exception\InvalidDateRangeException;
use DateTimeImmutable;
use DateTimeZone;

/**
 * Half-open instant range: `from` is inclusive, `to` is exclusive.
 *
 * The half-open shape is what lets two contiguous ranges add up to the whole
 * period without overlapping. Only `to < from` is rejected; `from == to` is a
 * valid empty range.
 */
final class DateRange
{
    private function __construct(
        private readonly DateTimeImmutable $from,
        private readonly DateTimeImmutable $to,
    ) {
    }

    public static function of(DateTimeImmutable $from, DateTimeImmutable $to): self
    {
        $from = $from->setTimezone(new DateTimeZone('UTC'));
        $to = $to->setTimezone(new DateTimeZone('UTC'));

        if ($to < $from) {
            throw InvalidDateRangeException::toBeforeFrom();
        }

        return new self($from, $to);
    }

    public function from(): DateTimeImmutable
    {
        return $this->from;
    }

    public function to(): DateTimeImmutable
    {
        return $this->to;
    }

    public function contains(DateTimeImmutable $instant): bool
    {
        $instant = $instant->setTimezone(new DateTimeZone('UTC'));

        return $instant >= $this->from && $instant < $this->to;
    }
}
