<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

trait HasSlugRouteKey
{
    public function getRouteKey(): mixed
    {
        $id = (int) $this->getKey();
        $prefix = $this->routeKeyPrefix();
        $source = $this->routeKeySourceValue();

        $slug = $source !== ''
            ? Str::slug(Str::limit($source, 80, ''))
            : '';

        $suffix = $prefix . $id;

        return $slug !== '' ? $slug . '-' . $suffix : $suffix;
    }

    public function resolveRouteBinding($value, $field = null): ?Model
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        if (is_numeric($value)) {
            return $this->newQuery()->whereKey((int) $value)->first();
        }

        if (preg_match('/(\d+)$/', $value, $matches)) {
            return $this->newQuery()->whereKey((int) $matches[1])->first();
        }

        return null;
    }

    protected function routeKeyPrefix(): string
    {
        return '';
    }

    protected function routeKeySourceColumn(): ?string
    {
        return null;
    }

    protected function routeKeySourceValue(): string
    {
        $column = $this->routeKeySourceColumn();

        if ($column === null) {
            return '';
        }

        return trim((string) data_get($this, $column));
    }
}
