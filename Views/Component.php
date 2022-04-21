<?php

namespace khokonc\mvc\Views;

use khokonc\mvc\Exceptions\ComponentNotFoundException;
use khokonc\mvc\Exceptions\InvalidComponentException;

trait Component
{

    private function getComponentDirectory($component = [])
    {
        $firstMatch = $component[0];
        $componentName = ltrim($firstMatch, '<x-');
        $componentName = rtrim($componentName, '>');
        $componentName = str_replace('.', '/', $componentName);
        $componentPath = self::BASE_VIEW . "/$componentName.php";
        if (!file_exists($componentPath)) {
            throw new ComponentNotFoundException("component name " . $componentName . " not found", 500);
        }
        return $componentPath;
    }


    private function hasComponent($string)
    {
        preg_match_all('~<x-[a-zA-z0-9.]*>|</x-[a-zA-z0-9.]*>~mi', $string, $matches);
        if (count($matches[0]) === 2) {
            $matches = $matches[0];
            $prefix = rtrim(ltrim($matches[0], '<x-'), '>');
            $postfix = rtrim(ltrim($matches[1], '</x-'), '>');

            if ($prefix === $postfix) {
                return $matches;
            }
            throw new InvalidComponentException([$prefix, $postfix]);
        }
        return false;
    }

    private function sliceViewContent(string $content, array $matches)
    {
        $content = str_replace($matches[0], '', $content);
        $content = str_replace($matches[1], '', $content);
        return $content;
    }


    private function renderComponent($content)
    {
        $hasComponent = $this->hasComponent($content);
        $componentDirectory = $hasComponent ? $this->getComponentDirectory($hasComponent) : false;
        $viewContent = $componentDirectory ? $this->sliceViewContent($content, $hasComponent) : false;

        if ($viewContent) {
            return $this->getViewContent($componentDirectory, [
                'slot' => $viewContent
            ]);
        }
        return $content;
    }

}