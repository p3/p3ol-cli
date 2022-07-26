<?php

namespace App\Parsers\AtomData;

use Illuminate\Support\Collection;

class Man
{
    public static function parse(string $atomName, ?string $data): mixed
    {
        return match ($atomName) {
            'man_start_object' => self::manStartObject($data),
            'man_set_context_globalid' => self::manSetContextGlobalId($data),
            'man_set_context_relative' => hexdec($data),
            'man_append_data' => json_encode(hex2binary($data)),
            'man_replace_data' => json_encode(hex2binary($data)),
            default => $data
        };
    }

    public static function manStartObject(mixed $data): string
    {
        $type = match (hexdec(str($data)->substr(0, 2))) {
            0 => 'org_group',
            1 => 'ind_group',
            2 => 'dms_list',
            3 => 'sms_list',
            4 =>'dss_list',
            5 => 'sss_list',
            6 => 'trigger',
            7 => 'ornament',
            8 => 'view',
            9 => 'edit_view',
            12 => 'range',
            13 =>'select_range',
            17 => 'tool_group',
            18 => 'tab_group',
            19 => 'tab_page',
            default => null
        };

        $data = json_encode(hex2binary(str($data)->substr(2)));

        return $data ? "$type, $data" : $type;
    }

    public static function manSetContextGlobalId(string $data): string
    {
        if (strlen($data) === 2) {
            return hexdec($data);
        }

        return collect($data)
            ->flatMap(fn (string $hex) => str_split($hex, 2))
            ->map(fn (string $hex) => hexdec($hex))
            ->when(strlen($data) === 8, function (Collection $results) {
                return implode('-', [$results[0], $results[1], (($results[2] * 256) + $results[3])]);
            }, function (Collection $results) {
                return implode('-', [$results[0], ($results[1] * 256) + $results[2]]);
            });
    }
}
