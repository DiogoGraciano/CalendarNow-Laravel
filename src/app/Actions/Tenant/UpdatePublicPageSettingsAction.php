<?php

namespace App\Actions\Tenant;

use App\Support\ThemeResolver;
use Illuminate\Http\RedirectResponse;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class UpdatePublicPageSettingsAction
{
    use AsAction, WithAttributes;

    public function asController(ActionRequest $request): RedirectResponse
    {
        $this->fillFromRequest($request);
        $validated = $this->validateAttributes();
        $this->handle($validated, $request);

        return redirect()
            ->back()
            ->with('success', 'Configurações salvas com sucesso');
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    public function handle(array $validated, ?ActionRequest $request = null): void
    {
        $tenant = tenant();

        $tenant->update([
            'primary_color' => $validated['primary_color'] ?? null,
            'secondary_color' => $validated['secondary_color'] ?? null,
            'hero_title' => $validated['hero_title'] ?? null,
            'hero_subtitle' => $validated['hero_subtitle'] ?? null,
            'show_employees_section' => $validated['show_employees_section'] ?? true,
            'seo_home_title' => $validated['seo_home_title'] ?? null,
            'seo_home_description' => $validated['seo_home_description'] ?? null,
            'seo_booking_title' => $validated['seo_booking_title'] ?? null,
            'seo_booking_description' => $validated['seo_booking_description'] ?? null,
            'theme' => $validated['theme'] ?? 'default',
        ]);

        if ($request && $request->hasFile('logo')) {
            $path = $request->file('logo')->store('logos', 'public');
            $tenant->update(['logo' => '/storage/' . $path]);
        }

        if ($request && $request->hasFile('favicon')) {
            $path = $request->file('favicon')->store('favicons', 'public');
            $tenant->update(['favicon' => '/storage/' . $path]);
        }
    }

    public function rules(): array
    {
        return [
            'primary_color' => 'nullable|string|max:20',
            'secondary_color' => 'nullable|string|max:20',
            'hero_title' => 'nullable|string|max:255',
            'hero_subtitle' => 'nullable|string|max:1000',
            'show_employees_section' => 'nullable|boolean',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp,svg|max:2048',
            'favicon' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp,ico,svg|max:1024',
            'seo_home_title' => 'nullable|string|max:70',
            'seo_home_description' => 'nullable|string|max:160',
            'seo_booking_title' => 'nullable|string|max:70',
            'seo_booking_description' => 'nullable|string|max:160',
            'theme' => 'nullable|string|in:'.implode(',', array_keys(ThemeResolver::available())),
        ];
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user() !== null;
    }
}
