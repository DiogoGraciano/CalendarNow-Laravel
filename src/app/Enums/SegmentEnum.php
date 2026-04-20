<?php

namespace App\Enums;

enum SegmentEnum: string
{
    case BeautyAesthetics = 'beauty_aesthetics';
    case HealthWellness = 'health_wellness';
    case Education = 'education';
    case Consulting = 'consulting';
    case Technology = 'technology';
    case GeneralServices = 'general_services';
    case Food = 'food';
    case Fitness = 'fitness';
    case Fashion = 'fashion';
    case Automotive = 'automotive';

    public function label(): string
    {
        return __("segments.{$this->value}");
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    public static function toSelectArray(): array
    {
        return array_map(fn (self $case) => [
            'value' => $case->value,
            'label' => $case->label(),
        ], self::cases());
    }
}
