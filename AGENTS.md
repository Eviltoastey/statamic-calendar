# AGENTS.md

This file provides guidance to coding agents when working with code in this repository.

## Project Overview

**Statamic Calendar** — Recurring events and cached occurrences for Statamic.

This is the addon source code for `el-schneider/statamic-calendar`. It is symlinked into sibling sandbox projects for testing.

## Folder Structure

```
../statamic-calendar/              # This addon (source code)
../statamic-calendar-test/         # Statamic v5 sandbox
../statamic-calendar-test-v6/      # Statamic v6 sandbox
```

## Development Commands

### Code Quality

```bash
prettier --check .
prettier --write .
./vendor/bin/pint --test
./vendor/bin/pint
```

### Testing

```bash
./vendor/bin/pest
./vendor/bin/pest --filter=SomeTest
```

### Integration Testing with Live App

Both sandboxes are available via Herd:

- **v5**: `http://statamic-calendar-test.test`
- **v6**: `http://statamic-calendar-test-v6.test`
- **CP Login**: `agent@agent.md` / `agent`

Run artisan commands from the addon directory:

```bash
php ../statamic-calendar-test/artisan occurrences:rebuild
php ../statamic-calendar-test/artisan cache:clear
```

See logs at `../statamic-calendar-test/storage/logs/laravel.log` when debugging.

## Architecture

- **`src/Tags/Events.php`** — Antlers tags: `{{ events }}`, `{{ events:current_occurrence }}`, `{{ events:next_occurrences }}`, `{{ events:for_organizer }}`
- **`src/Occurrences/OccurrenceResolver.php`** — Resolves RRULE-based recurrence patterns into concrete `Occurrence` instances
- **`src/Occurrences/OccurrenceCache.php`** — Materializes occurrences into Laravel cache for fast listing
- **`src/Occurrences/Occurrence.php`** — Single occurrence DTO, generates URLs based on configured strategy
- **`src/Occurrences/OccurrenceData.php`** — Cached occurrence DTO (serializable)
- **`src/Http/Controllers/OccurrenceController.php`** — Show controller for `date_segments` URL strategy
- **`src/Listeners/RebuildOccurrenceCacheOnEntryChange.php`** — Auto-rebuild cache on entry save/delete
- **`config/statamic-calendar.php`** — Field mapping, URL strategy, cache settings
- **`resources/views/statamic-calendar/`** — Default Antlers templates (index + show)

## URL Strategies

The addon supports two strategies (configured in `config/statamic-calendar.php`):

- **`query_string`** (default) — Uses Statamic's native collection routing. No addon routes registered. Occurrence URLs: `/events/slug?date=2025-01-06`
- **`date_segments`** (opt-in) — Addon registers its own route. Occurrence URLs: `/events/2025/01/06/slug`

## Off-Limits Files

- **`vendor/`** — Managed by Composer.
- **`composer.lock`** — Managed by Composer.
