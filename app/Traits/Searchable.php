<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait Searchable
{
    /**
     * Scope a query to search for a term across one or more columns.
     *
     * Performs accent-insensitive search using PostgreSQL's unaccent() extension,
     * ordering results by relevance via pg_trgm's similarity() function.
     */
    public function scopeSearch(Builder $query, ?string $term, array|string $columns): Builder
    {
        if (blank($term) || blank($columns)) {
            return $query;
        }

        $columns = (array) $columns;

        $query->where(function (Builder $q) use ($columns, $term) {
            foreach ($columns as $column) {
                $q->orWhereRaw("unaccent({$column}) ILIKE unaccent(?)", ["%{$term}%"]);
            }
        });

        return $query->orderByRaw(
            $this->buildSimilarityOrder($columns),
            array_fill(0, count($columns), $term),
        );
    }

    /**
     * Build the ORDER BY clause using pg_trgm similarity.
     *
     * For multiple columns, uses GREATEST() to rank by the best match.
     */
    private function buildSimilarityOrder(array $columns): string
    {
        $expressions = array_map(
            fn (string $column) => "similarity(unaccent({$column}), unaccent(?))",
            $columns,
        );

        return count($expressions) > 1
            ? 'GREATEST('.implode(', ', $expressions).') DESC'
            : $expressions[0].' DESC';
    }
}
