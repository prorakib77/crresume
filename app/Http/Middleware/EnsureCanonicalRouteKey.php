<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCanonicalRouteKey
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->isMethod('GET') && !$request->isMethod('HEAD')) {
            return $next($request);
        }

        $route = $request->route();

        if (!$route || !$route->getName()) {
            return $next($request);
        }

        $parameters = $route->parameters();

        foreach ($parameters as $parameterName => $parameterValue) {
            if (!$parameterValue instanceof Model) {
                continue;
            }

            $originalValue = (string) ($route->originalParameter($parameterName) ?? '');
            $canonicalValue = (string) $parameterValue->getRouteKey();

            if ($originalValue === '' || $canonicalValue === '' || $originalValue === $canonicalValue) {
                continue;
            }

            $canonicalUrl = route($route->getName(), $parameters);
            $queryString = $request->getQueryString();

            if ($queryString) {
                $separator = str_contains($canonicalUrl, '?') ? '&' : '?';
                $canonicalUrl .= $separator . $queryString;
            }

            return redirect()->to($canonicalUrl, 301);
        }

        return $next($request);
    }
}
