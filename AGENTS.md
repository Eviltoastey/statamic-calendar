# AGENTS.md

This file provides guidance to coding agents when working with code in this repository.

## Project Overview

**Statamic Calendar**. Recurring events and cached occurrences for Statamic v5/v6. Uses RFC 5545 (RRULE) recurrence via `rlanvin/php-rrule`.

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

Two sandboxes are available:

- **v5**: `http://statamic-calendar-test.test`
- **v6**: `http://statamic-calendar-test-v6.test`

**Credentials:**

- Email: `agent@agent.md`
- Password: `agent`
- Login URL: `http://statamic-calendar-test.test/cp`

Run artisan from the addon directory:

```bash
php ../statamic-calendar-test/artisan occurrences:rebuild
```

See logs at `../statamic-calendar-test/storage/logs/laravel.log` when debugging.

## Off-Limits Files

- **`vendor/`** — Managed by Composer.
