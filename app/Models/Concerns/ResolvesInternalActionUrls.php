<?php

namespace App\Models\Concerns;

use Illuminate\Support\Str;

trait ResolvesInternalActionUrls
{
    protected function appendQueryString(string $url, ?string $queryString = null): string
    {
        if (blank($queryString)) {
            return $url;
        }

        return $url . (str_contains($url, '?') ? '&' : '?') . $queryString;
    }

    protected function normalizeInternalActionPath(string $rawActionUrl): ?string
    {
        $trimmed = trim($rawActionUrl);
        if ($trimmed === '') {
            return null;
        }

        if (Str::startsWith($trimmed, ['mailto:', 'tel:', '#'])) {
            return null;
        }

        if (Str::startsWith($trimmed, ['http://', 'https://', '//'])) {
            $parsed = parse_url(Str::startsWith($trimmed, '//') ? 'https:' . $trimmed : $trimmed);
            if (!is_array($parsed)) {
                return null;
            }

            $host = $this->normalizeActionHost((string) ($parsed['host'] ?? ''));
            if ($host === '' || !$this->isInternalActionHost($host)) {
                return null;
            }

            $path = '/' . ltrim((string) ($parsed['path'] ?? '/'), '/');
            $query = isset($parsed['query']) ? (string) $parsed['query'] : '';

            return $query !== '' ? ($path . '?' . $query) : $path;
        }

        return '/' . ltrim($trimmed, '/');
    }

    protected function isInternalActionHost(string $host): bool
    {
        return in_array(
            $this->normalizeActionHost($host),
            $this->internalActionHosts(),
            true
        );
    }

    /**
     * @return array<int, string>
     */
    protected function internalActionHosts(): array
    {
        $requestHost = app()->bound('request') ? (string) app('request')->getHost() : '';
        $appHost = (string) parse_url((string) config('app.url'), PHP_URL_HOST);
        $legacyHosts = config('app.internal_action_legacy_hosts', []);

        return array_values(array_unique(array_filter(array_map(
            fn ($host) => $this->normalizeActionHost((string) $host),
            array_merge($legacyHosts, [
                $requestHost,
                $appHost,
                'localhost',
                '127.0.0.1',
                '::1',
            ])
        ))));
    }

    protected function normalizeActionHost(string $host): string
    {
        return strtolower((string) preg_replace('/^www\./i', '', trim($host)));
    }
}
