<?php

declare(strict_types=1);

namespace App\Presentation\Http\Response\Alerts;

class AlertItems
{
    public function __construct(
        public int     $id,
        public string  $alert_name,
        public string  $alert_type_label,
        public string  $condition_quality,
        public string  $condition_label,
        public string  $condition_symbol,
        public float   $threshold_value,
        public string  $frequency,
        public string  $frequency_label,
        public bool    $is_active,
        public string  $created_at,
        public ?string $last_triggered_at,
        public ?string $stock_symbol,
        public ?string $name,
        public float   $price,
        public ?string $currency,
        public float   $market_cap,
        public ?string $logo_url,
        public float   $change_percent,
    )
    {}
}
