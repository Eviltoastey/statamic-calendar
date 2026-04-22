<?php

declare(strict_types=1);

namespace ElSchneider\StatamicCalendar\Http\Controllers;

use Carbon\Carbon;
use ElSchneider\StatamicCalendar\Contracts\OccurrenceTransformer;
use ElSchneider\StatamicCalendar\Facades\Occurrences;
use ElSchneider\StatamicCalendar\Occurrences\OccurrenceData;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Statamic\Entries\Entry;
use Statamic\Facades\Entry as EntryFacade;

class ApiOccurrenceController
{
    public function __construct(
        private readonly OccurrenceTransformer $transformer,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $from = $request->has('from') ? Carbon::parse($request->query('from')) : Carbon::now();
        $to = $request->has('to') ? Carbon::parse($request->query('to')) : null;
        $sort = $request->query('sort', 'asc') === 'desc' ? 'desc' : 'asc';
        $tags = $request->query('tags');
        $organizer = $request->query('organizer');
        $page = max(1, $request->integer('page', 1));
        $perPage = min(100, max(1, $request->integer('per_page', 20)));

        $occurrences = Occurrences::all()
            ->filter(fn (OccurrenceData $o) => $o->start->gte($from))
            ->when($to, fn ($c) => $c->filter(fn (OccurrenceData $o) => $o->start->lte($to)));

        if ($tags) {
            $tagSlugs = array_filter(explode(',', (string) $tags));
            $occurrences = $occurrences->filter(fn (OccurrenceData $o) => $o->hasAnyTag($tagSlugs));
        }

        if ($organizer) {
            $occurrences = $occurrences->filter(fn (OccurrenceData $o) => $o->organizerId === $organizer);
        }

        $occurrences = $sort === 'desc'
            ? $occurrences->sortByDesc(fn (OccurrenceData $o) => $o->start)
            : $occurrences->sortBy(fn (OccurrenceData $o) => $o->start);

        $total = $occurrences->count();
        $lastPage = max(1, (int) ceil($total / $perPage));
        $paged = $occurrences->slice(($page - 1) * $perPage, $perPage)->values();

        $entries = $this->getEntries($paged);

        return new JsonResponse([
            'data' => $paged
                ->map(fn (OccurrenceData $o) => $this->transformer->transform(
                    $o,
                    $entries[$o->entryId] ?? null,
                ))
                ->values()
                ->all(),
            'meta' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'last_page' => $lastPage,
            ],
        ]);
    }

    /**
     * @return Collection<string, Entry>
     */
    private function getEntries(Collection $occurrences): Collection
    {
        $entryIds = $occurrences->pluck('entryId')->unique()->values()->all();

        return EntryFacade::query()
            ->whereIn('id', $entryIds)
            ->get()
            ->keyBy(fn (Entry $e) => (string) $e->id());
    }
}
