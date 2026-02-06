# Statamic Calendar

Recurring events and cached occurrences for Statamic.

## Features

- Define complex recurrence patterns using RFC 5545 (RRULE) via `rlanvin/php-rrule`
- Materialize occurrences into Laravel cache for fast listings
- Antlers tags for listing, current occurrence, and next occurrences
- Two URL strategies: query string (default, Statamic-native) or date segments

## Installation

```bash
composer require el-schneider/statamic-calendar
```

Publish the config:

```bash
php artisan vendor:publish --tag=statamic-calendar
```

## URL Strategies

The addon supports two strategies for occurrence URLs. Configure in `config/statamic-calendar.php`:

### Query String (default)

Uses Statamic's native collection routing. The addon doesn't register any routes — your collection config handles everything:

```yaml
# content/collections/events.yaml
route: '/events/{slug}'
```

Occurrence URLs look like `/events/my-event?date=2025-03-15`.

### Date Segments (opt-in)

For SEO-friendly date-based URLs like `/events/2025/03/15/my-event`. Enable in config:

```php
'url' => [
    'strategy' => 'date_segments',
    'date_segments' => [
        'prefix' => 'events',
    ],
],
```

The addon registers a route at `/{prefix}/{year}/{month}/{day}/{slug}`.

## Setting Up Templates

### Events Index

Create a page or route that uses the `{{ events }}` tag. For example, add to `routes/web.php`:

```php
Route::statamic('events', 'events/index', [
    'title' => 'Upcoming Events',
]);
```

Then create `resources/views/events/index.antlers.html`:

```antlers
<h1>Upcoming Events</h1>

{{ events from="now" limit="20" }}
    <a href="{{ url }}">
        <h2>{{ title }}</h2>
        <p>{{ start format="l, M j, Y" }}</p>
        {{ if is_recurring }}
            <p>{{ recurrence_description }}</p>
        {{ /if }}
    </a>
{{ /events }}
```

### Event Show Page

Set the collection template to `events/show`, then create `resources/views/events/show.antlers.html`:

```antlers
<h1>{{ title }}</h1>

{{ events:current_occurrence }}
    <p>{{ start format="l, F j, Y" }}</p>
    {{ if !is_all_day }}
        <p>{{ start format="g:i A" }}{{ if end }} – {{ end format="g:i A" }}{{ /if }}</p>
    {{ else }}
        <p>All day</p>
    {{ /if }}
    {{ if is_recurring }}
        <p>Repeats: {{ recurrence_description }}</p>
    {{ /if }}
{{ /events:current_occurrence }}

{{ events:next_occurrences :entry="id" limit="5" }}
    <a href="{{ url }}">{{ start format="M j, Y" }}</a>
{{ /events:next_occurrences }}
```

The `{{ events:current_occurrence }}` tag reads the `?date=` query parameter and resolves the matching occurrence for the current entry. When using the `date_segments` strategy, the date is extracted from the URL instead.

## Antlers Tags

### `{{ events }}`

Lists occurrences from the cache (or resolves them live for non-default collections).

| Parameter | Description | Default |
|-----------|-------------|---------|
| `from` | Start date | `now` |
| `to` | End date | — |
| `limit` | Max occurrences | — |
| `collection` | Collection handle | config value |
| `tags` / `event_tags` | Filter by taxonomy terms | — |

### `{{ events:current_occurrence }}`

Resolves the current occurrence for the entry in context, based on the `?date=` query param. Use as a tag pair — variables available inside:

- `start` — Carbon date
- `end` — Carbon date (nullable)
- `is_all_day` — boolean
- `is_recurring` — boolean
- `recurrence_description` — human-readable recurrence rule
- `occurrence_url` — the occurrence URL

### `{{ events:next_occurrences }}`

Lists upcoming occurrences for a specific entry.

| Parameter | Description | Default |
|-----------|-------------|---------|
| `entry` | Entry ID | current context `id` |
| `from` | Start date | `now` |
| `to` | End date | — |
| `limit` | Max occurrences | `5` |

### `{{ events:for_organizer }}`

Lists upcoming occurrences for an organizer (from cache).

| Parameter | Description | Default |
|-----------|-------------|---------|
| `organizer` | Organizer entry ID | current context `id` |
| `limit` | Max occurrences | `5` |

## Configuration

Key options in `config/statamic-calendar.php`:

| Key | Description | Default |
|-----|-------------|---------|
| `collection` | Event collection handle | `events` |
| `fields.dates.handle` | Grid field handle | `dates` |
| `fields.dates.keys.*` | Sub-field key mapping | 1:1 mapping |
| `url.strategy` | `query_string` or `date_segments` | `query_string` |
| `url.query_string.param` | Query parameter name | `date` |
| `url.date_segments.prefix` | URL prefix for date segments | `events` |
| `cache.key` | Cache store key | `statamic_calendar.occurrences` |
| `cache.days_ahead` | Recurrence expansion window | `365` |

## Cache

Occurrences are materialized into Laravel's cache for fast listing. The cache rebuilds automatically when entries are saved or deleted.

Manual rebuild:

```bash
php artisan occurrences:rebuild
```

## Example Blueprint

Publish the example events blueprint:

```bash
php artisan vendor:publish --tag=statamic-calendar-examples
```
