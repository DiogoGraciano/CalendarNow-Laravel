<?php

namespace App\Support;

class ThemeResolver
{
    /** @var array<string, array{label: string, description: string}> */
    public const THEMES = [
        'default' => [
            'label' => 'Clássico',
            'description' => 'Layout limpo e profissional',
        ],
        'modern' => [
            'label' => 'Moderno',
            'description' => 'Design contemporâneo e arrojado',
        ],
    ];

    public static function current(): string
    {
        $theme = tenant()?->theme ?? 'default';

        return array_key_exists($theme, self::THEMES) ? $theme : 'default';
    }

    public static function viewPath(string $view): string
    {
        return 'themes.'.self::current().'.'.$view;
    }

    public static function cssAsset(): string
    {
        return 'resources/views/themes/'.self::current().'/css/theme.css';
    }

    public static function jsAsset(): string
    {
        return 'resources/views/themes/'.self::current().'/js/theme.ts';
    }

    /**
     * @return array<string, array{label: string, description: string}>
     */
    public static function available(): array
    {
        return self::THEMES;
    }
}
