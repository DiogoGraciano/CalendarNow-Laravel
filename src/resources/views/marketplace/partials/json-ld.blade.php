@php
    $websiteSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'WebSite',
        'name' => 'CalendarNow',
        'url' => url('/marketplace'),
        'description' => __('marketplace.description'),
        'potentialAction' => [
            '@type' => 'SearchAction',
            'target' => url('/marketplace/search') . '?search={search_term_string}',
            'query-input' => 'required name=search_term_string',
        ],
    ];

    $itemListSchema = null;
    if (isset($tenants) && $tenants->isNotEmpty()) {
        $items = [];
        $position = 1;
        foreach ($tenants as $tenant) {
            $domain = $tenant->domains->first()?->domain;
            if (! $domain) {
                continue;
            }
            $tenantUrl = request()->getScheme() . '://' . $domain;
            $item = [
                '@type' => 'ListItem',
                'position' => $position++,
                'item' => [
                    '@type' => 'LocalBusiness',
                    'name' => $tenant->name,
                    'url' => $tenantUrl,
                ],
            ];
            if ($tenant->city || $tenant->state || $tenant->country) {
                $address = ['@type' => 'PostalAddress'];
                if ($tenant->city) { $address['addressLocality'] = $tenant->city; }
                if ($tenant->state) { $address['addressRegion'] = $tenant->state; }
                if ($tenant->country) { $address['addressCountry'] = $tenant->country; }
                $item['item']['address'] = $address;
            }
            if ($tenant->services_count) {
                $item['item']['hasOfferCatalog'] = [
                    '@type' => 'OfferCatalog',
                    'numberOfItems' => $tenant->services_count,
                ];
            }
            $items[] = $item;
        }
        $itemListSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'ItemList',
            'name' => __('marketplace.title'),
            'numberOfItems' => $tenants->total(),
            'itemListElement' => $items,
        ];
    }
@endphp
<script type="application/ld+json">{!! json_encode($websiteSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}</script>
@if($itemListSchema)
<script type="application/ld+json">{!! json_encode($itemListSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}</script>
@endif
