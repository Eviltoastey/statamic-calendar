<?php

declare(strict_types=1);

if (config('statamic-calendar.url.strategy') !== 'date_segments') {
    return;
}

use ElSchneider\StatamicCalendar\Http\Controllers\OccurrenceController;
use Illuminate\Support\Facades\Route;

$prefix = mb_trim((string) config('statamic-calendar.url.date_segments.prefix', 'calendar'), '/');

Route::get('/'.$prefix.'/{year}/{month}/{day}/{slug}', [OccurrenceController::class, 'show'])
    ->where('year', '[0-9]{4}')
    ->where('month', '[0-9]{2}')
    ->where('day', '[0-9]{2}')
    ->name('calendar.occurrence');
