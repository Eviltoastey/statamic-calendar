<?php

declare(strict_types=1);

namespace ElSchneider\StatamicCalendar\Http\Controllers;

use ElSchneider\StatamicCalendar\Facades\Occurrences;
use ElSchneider\StatamicCalendar\Ics\IcsGenerator;
use ElSchneider\StatamicCalendar\Occurrences\OccurrenceData;
use Illuminate\Http\Response;

class IcsController
{
    public function __construct(
        private IcsGenerator $generator
    ) {}

    /**
     * Full calendar feed — subscribable by calendar apps.
     */
    public function feed(): Response
    {
        $name = (string) config('statamic-calendar.ics.calendar_name', config('app.name', 'Calendar'));

        $body = $this->generator->feed(Occurrences::all(), $name);

        return $this->icsResponse($body);
    }

    /**
     * Single occurrence download — "Add to calendar" button.
     *
     * The occurrence ID format is "{entry_id}-{Y-m-d-His}".
     */
    public function download(string $occurrenceId): Response
    {
        $occurrence = Occurrences::all()->first(
            fn (OccurrenceData $o) => $o->id === $occurrenceId
        );

        if (! $occurrence) {
            abort(404);
        }

        $body = $this->generator->single($occurrence);
        $filename = str_replace(['/', '\\', ' '], '-', $occurrence->slug).'.ics';

        return $this->icsResponse($body, $filename);
    }

    private function icsResponse(string $body, ?string $downloadFilename = null): Response
    {
        $headers = ['Content-Type' => 'text/calendar; charset=utf-8'];

        if ($downloadFilename) {
            $headers['Content-Disposition'] = 'attachment; filename="'.$downloadFilename.'"';
        }

        return new Response($body, 200, $headers);
    }
}
