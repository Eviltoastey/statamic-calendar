<?php

declare(strict_types=1);

namespace ElSchneider\StatamicCalendar\Ics;

use Carbon\Carbon;
use ElSchneider\StatamicCalendar\Occurrences\OccurrenceData;
use Illuminate\Support\Collection;

class IcsGenerator
{
    /**
     * Generate a full iCalendar document from a collection of occurrences.
     *
     * @param  Collection<int, OccurrenceData>  $occurrences
     */
    public function feed(Collection $occurrences, string $calendarName, string $prodId = '-//Statamic//Calendar//EN'): string
    {

        $lines = [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:'.$this->escape($prodId),
            'X-WR-CALNAME:'.$this->escape($calendarName),
            'CALSCALE:GREGORIAN',
            'METHOD:PUBLISH',
        ];

        foreach ($occurrences as $occurrence) {
            $lines = array_merge($lines, $this->vevent($occurrence));
        }

        $lines[] = 'END:VCALENDAR';

        return implode("\r\n", $lines)."\r\n";
    }

    /**
     * Generate a single-event iCalendar document.
     */
    public function single(OccurrenceData $occurrence, string $prodId = '-//Statamic//Calendar//EN'): string
    {
        return $this->feed(collect([$occurrence]), $occurrence->title, $prodId);
    }

    /**
     * @return array<string>
     */
    private function vevent(OccurrenceData $occurrence): array
    {
        $lines = ['BEGIN:VEVENT'];

        $lines[] = 'UID:'.$this->escape($occurrence->id);
        $lines[] = 'DTSTAMP:'.$this->formatUtc(Carbon::now());
        $lines[] = 'SUMMARY:'.$this->escape($occurrence->title);

        if ($occurrence->isAllDay) {
            $lines[] = 'DTSTART;VALUE=DATE:'.$occurrence->start->format('Ymd');
            if ($occurrence->end) {
                // iCal spec: all-day end date is exclusive, so add one day
                $lines[] = 'DTEND;VALUE=DATE:'.$occurrence->end->copy()->addDay()->format('Ymd');
            }
        } else {
            $lines[] = 'DTSTART:'.$this->formatUtc($occurrence->start);
            if ($occurrence->end) {
                $lines[] = 'DTEND:'.$this->formatUtc($occurrence->end);
            }
        }

        if ($occurrence->teaser) {
            $lines[] = 'DESCRIPTION:'.$this->escape($occurrence->teaser);
        }

        if ($occurrence->url) {
            $lines[] = 'URL:'.$occurrence->url;
        }

        $lines[] = 'END:VEVENT';

        return $lines;
    }

    private function formatUtc(Carbon $date): string
    {
        return $date->utc()->format('Ymd\THis\Z');
    }

    /**
     * Escape text values per RFC 5545 §3.3.11.
     */
    private function escape(string $value): string
    {
        $value = str_replace('\\', '\\\\', $value);
        $value = str_replace(';', '\;', $value);
        $value = str_replace(',', '\,', $value);
        $value = str_replace("\n", '\n', $value);

        return $value;
    }
}
