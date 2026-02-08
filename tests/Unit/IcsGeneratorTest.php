<?php

declare(strict_types=1);

use Carbon\Carbon;
use ElSchneider\StatamicCalendar\Ics\IcsGenerator;
use ElSchneider\StatamicCalendar\Occurrences\OccurrenceData;

beforeEach(function () {
    $this->generator = new IcsGenerator;
    Carbon::setTestNow(Carbon::parse('2026-01-15 10:00:00', 'UTC'));
});

afterEach(fn () => Carbon::setTestNow());

function makeOccurrence(array $overrides = []): OccurrenceData
{
    return OccurrenceData::fromArray(array_merge([
        'id' => 'abc-123-2026-03-06-150000',
        'entry_id' => 'abc-123',
        'title' => 'Laracon Online',
        'slug' => 'laracon-online',
        'teaser' => null,
        'tags' => [],
        'start' => '2026-03-06T15:00:00+00:00',
        'end' => '2026-03-06T16:00:00+00:00',
        'is_all_day' => false,
        'is_recurring' => false,
        'recurrence_description' => null,
        'url' => '/events/laracon-online',
    ], $overrides));
}

test('generates a valid icalendar document', function () {
    $occurrence = makeOccurrence();
    $ics = $this->generator->single($occurrence);

    expect($ics)
        ->toContain('BEGIN:VCALENDAR')
        ->toContain('VERSION:2.0')
        ->toContain('BEGIN:VEVENT')
        ->toContain('END:VEVENT')
        ->toContain('END:VCALENDAR')
        ->toContain('SUMMARY:Laracon Online')
        ->toContain('UID:abc-123-2026-03-06-150000');
});

test('formats timed events as UTC datetime', function () {
    $occurrence = makeOccurrence([
        'start' => '2026-03-06T15:00:00+00:00',
        'end' => '2026-03-06T16:00:00+00:00',
    ]);

    $ics = $this->generator->single($occurrence);

    expect($ics)
        ->toContain('DTSTART:20260306T150000Z')
        ->toContain('DTEND:20260306T160000Z');
});

test('formats all-day events as VALUE=DATE with exclusive end', function () {
    $occurrence = makeOccurrence([
        'start' => '2026-03-06T00:00:00+00:00',
        'end' => '2026-03-06T23:59:59+00:00',
        'is_all_day' => true,
    ]);

    $ics = $this->generator->single($occurrence);

    expect($ics)
        ->toContain('DTSTART;VALUE=DATE:20260306')
        ->toContain('DTEND;VALUE=DATE:20260307');
});

test('includes description when teaser is present', function () {
    $occurrence = makeOccurrence(['teaser' => 'A great conference']);
    $ics = $this->generator->single($occurrence);

    expect($ics)->toContain('DESCRIPTION:A great conference');
});

test('omits description when teaser is null', function () {
    $occurrence = makeOccurrence(['teaser' => null]);
    $ics = $this->generator->single($occurrence);

    expect($ics)->not->toContain('DESCRIPTION:');
});

test('includes url', function () {
    $occurrence = makeOccurrence(['url' => '/events/laracon-online']);
    $ics = $this->generator->single($occurrence);

    expect($ics)->toContain('URL:/events/laracon-online');
});

test('escapes special characters in text fields', function () {
    $occurrence = makeOccurrence([
        'title' => 'Event; with, special\\chars',
        'teaser' => "Line one\nLine two",
    ]);

    $ics = $this->generator->single($occurrence);

    expect($ics)
        ->toContain('SUMMARY:Event\; with\, special\\\\chars')
        ->toContain('DESCRIPTION:Line one\nLine two');
});

test('feed wraps multiple events in a single calendar', function () {
    $occurrences = collect([
        makeOccurrence(['id' => 'event-1-2026-03-06-150000', 'title' => 'First']),
        makeOccurrence(['id' => 'event-2-2026-03-07-150000', 'title' => 'Second']),
    ]);

    $ics = $this->generator->feed($occurrences, 'My Calendar');

    // Single calendar wrapper
    expect(mb_substr_count($ics, 'BEGIN:VCALENDAR'))->toBe(1);
    expect(mb_substr_count($ics, 'END:VCALENDAR'))->toBe(1);

    // Two events
    expect(mb_substr_count($ics, 'BEGIN:VEVENT'))->toBe(2);

    expect($ics)
        ->toContain('X-WR-CALNAME:My Calendar')
        ->toContain('SUMMARY:First')
        ->toContain('SUMMARY:Second');
});

test('uses CRLF line endings per RFC 5545', function () {
    $ics = $this->generator->single(makeOccurrence());

    expect($ics)->toContain("\r\n");
    // No bare LF (every LF should be preceded by CR)
    expect(preg_match('/[^\r]\n/', $ics))->toBe(0);
});

test('event without end date omits DTEND', function () {
    $occurrence = makeOccurrence(['end' => null]);
    $ics = $this->generator->single($occurrence);

    expect($ics)->not->toContain('DTEND');
});
