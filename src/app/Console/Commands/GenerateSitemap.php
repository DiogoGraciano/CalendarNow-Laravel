<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;

#[Signature('sitemap:generate')]
#[Description('Generate the sitemap.xml for the marketplace and tenant pages')]
class GenerateSitemap extends Command
{
    public function handle(): int
    {
        $scheme = config('app.url_scheme', 'https');
        $centralDomain = config('tenancy.central_domains')[0] ?? 'localhost';
        $baseUrl = $scheme.'://'.$centralDomain;

        $sitemap = Sitemap::create()
            ->add(
                Url::create($baseUrl.'/marketplace')
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY)
                    ->setPriority(1.0)
            );

        $tenants = Tenant::query()
            ->whereHas('domains')
            ->with('domains')
            ->get();

        foreach ($tenants as $tenant) {
            $domain = $tenant->domains->first()?->domain;
            if (! $domain) {
                continue;
            }

            $tenantBaseUrl = $scheme.'://'.$domain;

            $sitemap->add(
                Url::create($tenantBaseUrl)
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                    ->setPriority(0.8)
            );

            $sitemap->add(
                Url::create($tenantBaseUrl.'/agendar')
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                    ->setPriority(0.7)
            );
        }

        $sitemap->writeToFile(public_path('sitemap.xml'));

        $this->info('Sitemap generated with '.($tenants->count() * 2 + 1).' URLs.');

        return self::SUCCESS;
    }
}
