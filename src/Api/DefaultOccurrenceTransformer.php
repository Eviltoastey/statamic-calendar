<?php

declare(strict_types=1);

namespace ElSchneider\StatamicCalendar\Api;

use ElSchneider\StatamicCalendar\Contracts\OccurrenceTransformer;
use ElSchneider\StatamicCalendar\Occurrences\OccurrenceData;
use Statamic\Entries\Entry;

class DefaultOccurrenceTransformer implements OccurrenceTransformer
{
    public function transform(OccurrenceData $occurrence, ?Entry $entry): array
    {
        return $occurrence->toArray();
    }
}
