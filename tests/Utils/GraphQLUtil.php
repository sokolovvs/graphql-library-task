<?php

namespace App\Tests\Utils;

class GraphQLUtil
{
    public static function inlineFilters(array $filters = [], string $name = 'filters'): string {
        $inlineFilters = '';
        if (!empty($filters)) {
            $inlineFilters = '{';
            foreach ($filters as $key => $value) {
                $value = is_numeric($value) ? $value : "\"$value\"";
                $inlineFilters .= "$key: $value";
            }
            $inlineFilters .= '}';
            $inlineFilters = "($name: $inlineFilters)";
        }

        return $inlineFilters;
    }
}