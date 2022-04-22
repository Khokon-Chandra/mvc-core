<?php

namespace khokonc\mvc\Routing\Routes;

use khokonc\mvc\Exceptions\InvalidRouteParametterException;

class RouteFactory
{

    private array $patterns = [
        '~/~',
        '~{[^\/{}]+}~'
    ];

    private array $replacements = [
        '/',
        '([0-9a-zA-Z-_]++)',
    ];

    public function create($path, $method, $callback)
    {
        return new Route(
            $this->parseUrl($path),
            $method,
            $callback
        );
    }

    private function parseUrl($path)
    {
        if (str_contains($path, '(')) {
            throw new InvalidRouteParametterException('Invalide route Parametter ', 500);
        }
        $with_regex = preg_replace($this->patterns, $this->replacements, $path);
        $with_regex = trim($with_regex, '\/?');
        $with_regex = trim($with_regex, '\/');
        $with_regex = empty($with_regex) ? '/' : $with_regex;

        $no_regex   = trim($path,'/') ?? '/';

        return [
            'with_regex' =>$with_regex,
            'no_regex' =>$no_regex
        ];
    }



}