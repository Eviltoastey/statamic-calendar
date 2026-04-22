<?php

declare(strict_types=1);

namespace ElSchneider\StatamicCalendar\Contracts;

use ElSchneider\StatamicCalendar\Occurrences\OccurrenceData;
use Statamic\Entries\Entry;

interface OccurrenceTransformer
{
    /** @return array<string, mixed> */
    public function transform(OccurrenceData $occurrence, ?Entry $entry): array;
}
