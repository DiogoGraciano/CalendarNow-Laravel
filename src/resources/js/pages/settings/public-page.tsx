import { type FormEvent } from 'react';
import { Head, router, useForm } from '@inertiajs/react';
import { Transition } from '@headlessui/react';

import HeadingSmall from '@/components/heading-small';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { useTranslation } from '@/hooks/use-translation';
import AppLayout from '@/layouts/app-layout';
import SettingsLayout from '@/layouts/settings/layout';
import { type BreadcrumbItem } from '@/types';
import UpdatePublicPageSettingsAction from '@/actions/App/Actions/Tenant/UpdatePublicPageSettingsAction';
import ShowPublicPageSettingsAction from '@/actions/App/Actions/Tenant/ShowPublicPageSettingsAction';

interface ThemeInfo {
    label: string;
    description: string;
}

interface Props {
    tenant: {
        name: string;
        logo: string | null;
        favicon: string | null;
        primary_color: string | null;
        secondary_color: string | null;
        hero_title: string | null;
        hero_subtitle: string | null;
        show_employees_section: boolean;
        seo_home_title: string | null;
        seo_home_description: string | null;
        seo_booking_title: string | null;
        seo_booking_description: string | null;
        theme: string;
    };
    availableThemes: Record<string, ThemeInfo>;
}

export default function PublicPage({ tenant, availableThemes }: Props) {
    const { t } = useTranslation();

    const { data, setData, processing, errors, recentlySuccessful } = useForm({
        primary_color: tenant.primary_color ?? '#3b82f6',
        secondary_color: tenant.secondary_color ?? '#10b981',
        hero_title: tenant.hero_title ?? '',
        hero_subtitle: tenant.hero_subtitle ?? '',
        logo: null as File | null,
        favicon: null as File | null,
        show_employees_section: tenant.show_employees_section,
        seo_home_title: tenant.seo_home_title ?? '',
        seo_home_description: tenant.seo_home_description ?? '',
        seo_booking_title: tenant.seo_booking_title ?? '',
        seo_booking_description: tenant.seo_booking_description ?? '',
        theme: tenant.theme ?? 'default',
    });

    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: t('settings.publicPage.title'),
            href: ShowPublicPageSettingsAction.url(),
        },
    ];

    const handleSubmit = (e: FormEvent) => {
        e.preventDefault();

        router.post(UpdatePublicPageSettingsAction.url(), {
            primary_color: data.primary_color,
            secondary_color: data.secondary_color,
            hero_title: data.hero_title,
            hero_subtitle: data.hero_subtitle,
            logo: data.logo,
            favicon: data.favicon,
            show_employees_section: data.show_employees_section ? '1' : '0',
            seo_home_title: data.seo_home_title,
            seo_home_description: data.seo_home_description,
            seo_booking_title: data.seo_booking_title,
            seo_booking_description: data.seo_booking_description,
            theme: data.theme,
        }, {
            preserveScroll: true,
            forceFormData: true,
        });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={t('settings.publicPage.title')} />

            <SettingsLayout>
                <div className="space-y-6">
                    <HeadingSmall
                        title={t('settings.publicPage.title')}
                        description={t('settings.publicPage.description')}
                    />

                    <form onSubmit={handleSubmit} className="max-w-xl space-y-8">
                        {/* Theme */}
                        <div className="space-y-4">
                            <h4 className="text-sm font-medium">
                                {t('settings.publicPage.themeTitle')}
                            </h4>
                            <p className="text-muted-foreground text-xs">
                                {t('settings.publicPage.themeDescription')}
                            </p>

                            <div className="grid gap-3 sm:grid-cols-2">
                                {Object.entries(availableThemes).map(([key, theme]) => (
                                    <button
                                        key={key}
                                        type="button"
                                        onClick={() => setData('theme', key)}
                                        className={`rounded-xl border-2 p-4 text-left transition-all duration-200 cursor-pointer ${
                                            data.theme === key
                                                ? 'border-primary bg-primary/5 shadow-sm'
                                                : 'border-border bg-card hover:border-primary/40 hover:shadow-sm'
                                        }`}
                                    >
                                        <div className="font-medium text-sm">{theme.label}</div>
                                        <div className="text-muted-foreground text-xs mt-1">
                                            {theme.description}
                                        </div>
                                    </button>
                                ))}
                            </div>
                            <InputError message={errors.theme} />
                        </div>

                        {/* Branding */}
                        <div className="space-y-4">
                            <h4 className="text-sm font-medium">
                                {t('settings.publicPage.brandingTitle')}
                            </h4>

                            <div className="grid gap-4 sm:grid-cols-2">
                                <div className="grid gap-2">
                                    <Label htmlFor="primary_color">
                                        {t('settings.publicPage.primaryColor')}
                                    </Label>
                                    <Input
                                        id="primary_color"
                                        type="color"
                                        value={data.primary_color}
                                        onChange={(e) => setData('primary_color', e.target.value)}
                                        className="h-10 w-full cursor-pointer"
                                    />
                                    <InputError message={errors.primary_color} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="secondary_color">
                                        {t('settings.publicPage.secondaryColor')}
                                    </Label>
                                    <Input
                                        id="secondary_color"
                                        type="color"
                                        value={data.secondary_color}
                                        onChange={(e) => setData('secondary_color', e.target.value)}
                                        className="h-10 w-full cursor-pointer"
                                    />
                                    <InputError message={errors.secondary_color} />
                                </div>
                            </div>
                        </div>

                        {/* Hero */}
                        <div className="space-y-4">
                            <h4 className="text-sm font-medium">Hero</h4>

                            <div className="grid gap-4">
                                <div className="grid gap-2">
                                    <Label htmlFor="hero_title">
                                        {t('settings.publicPage.heroTitle')}
                                    </Label>
                                    <Input
                                        id="hero_title"
                                        value={data.hero_title}
                                        onChange={(e) => setData('hero_title', e.target.value)}
                                        placeholder={t('settings.publicPage.heroTitlePlaceholder')}
                                    />
                                    <InputError message={errors.hero_title} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="hero_subtitle">
                                        {t('settings.publicPage.heroSubtitle')}
                                    </Label>
                                    <Textarea
                                        id="hero_subtitle"
                                        value={data.hero_subtitle}
                                        onChange={(e) => setData('hero_subtitle', e.target.value)}
                                        placeholder={t('settings.publicPage.heroSubtitlePlaceholder')}
                                        rows={3}
                                    />
                                    <InputError message={errors.hero_subtitle} />
                                </div>
                            </div>
                        </div>

                        {/* Logo & Favicon */}
                        <div className="space-y-4">
                            <h4 className="text-sm font-medium">
                                {t('settings.publicPage.logoTitle')}
                            </h4>

                            <div className="grid gap-4 sm:grid-cols-2">
                                <div className="grid gap-2">
                                    <Label htmlFor="logo">
                                        {t('settings.publicPage.logo')}
                                    </Label>
                                    <p className="text-muted-foreground text-xs">
                                        {t('settings.publicPage.logoDescription')}
                                    </p>
                                    {tenant.logo && (
                                        <img
                                            src={tenant.logo}
                                            alt="Logo"
                                            className="h-16 w-16 rounded-md border object-contain"
                                        />
                                    )}
                                    <Input
                                        id="logo"
                                        type="file"
                                        accept="image/jpeg,image/png,image/jpg,image/gif,image/webp,image/svg+xml"
                                        onChange={(e) => setData('logo', e.target.files?.[0] ?? null)}
                                    />
                                    <InputError message={errors.logo} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="favicon">
                                        {t('settings.publicPage.favicon')}
                                    </Label>
                                    <p className="text-muted-foreground text-xs">
                                        {t('settings.publicPage.faviconDescription')}
                                    </p>
                                    {tenant.favicon && (
                                        <img
                                            src={tenant.favicon}
                                            alt="Favicon"
                                            className="h-16 w-16 rounded-md border object-contain"
                                        />
                                    )}
                                    <Input
                                        id="favicon"
                                        type="file"
                                        accept="image/jpeg,image/png,image/jpg,image/gif,image/webp,image/x-icon,image/svg+xml"
                                        onChange={(e) => setData('favicon', e.target.files?.[0] ?? null)}
                                    />
                                    <InputError message={errors.favicon} />
                                </div>
                            </div>
                        </div>

                        {/* SEO */}
                        <div className="space-y-4">
                            <h4 className="text-sm font-medium">
                                {t('settings.publicPage.seoTitle')}
                            </h4>
                            <p className="text-muted-foreground text-xs">
                                {t('settings.publicPage.seoHelp')}
                            </p>

                            <div className="space-y-4">
                                <h5 className="text-xs font-medium text-muted-foreground uppercase tracking-wide">
                                    {t('settings.publicPage.seoHomeSectionTitle')}
                                </h5>
                                <div className="grid gap-4">
                                    <div className="grid gap-2">
                                        <Label htmlFor="seo_home_title">
                                            {t('settings.publicPage.seoMetaTitle')}
                                        </Label>
                                        <Input
                                            id="seo_home_title"
                                            value={data.seo_home_title}
                                            onChange={(e) => setData('seo_home_title', e.target.value)}
                                            placeholder={t('settings.publicPage.seoMetaTitlePlaceholder')}
                                            maxLength={70}
                                        />
                                        <InputError message={errors.seo_home_title} />
                                    </div>
                                    <div className="grid gap-2">
                                        <Label htmlFor="seo_home_description">
                                            {t('settings.publicPage.seoDescription')}
                                        </Label>
                                        <Textarea
                                            id="seo_home_description"
                                            value={data.seo_home_description}
                                            onChange={(e) => setData('seo_home_description', e.target.value)}
                                            placeholder={t('settings.publicPage.seoDescriptionPlaceholder')}
                                            maxLength={160}
                                            rows={2}
                                        />
                                        <InputError message={errors.seo_home_description} />
                                    </div>
                                </div>
                            </div>

                            <div className="space-y-4">
                                <h5 className="text-xs font-medium text-muted-foreground uppercase tracking-wide">
                                    {t('settings.publicPage.seoBookingSectionTitle')}
                                </h5>
                                <div className="grid gap-4">
                                    <div className="grid gap-2">
                                        <Label htmlFor="seo_booking_title">
                                            {t('settings.publicPage.seoMetaTitle')}
                                        </Label>
                                        <Input
                                            id="seo_booking_title"
                                            value={data.seo_booking_title}
                                            onChange={(e) => setData('seo_booking_title', e.target.value)}
                                            placeholder={t('settings.publicPage.seoBookingTitlePlaceholder')}
                                            maxLength={70}
                                        />
                                        <InputError message={errors.seo_booking_title} />
                                    </div>
                                    <div className="grid gap-2">
                                        <Label htmlFor="seo_booking_description">
                                            {t('settings.publicPage.seoDescription')}
                                        </Label>
                                        <Textarea
                                            id="seo_booking_description"
                                            value={data.seo_booking_description}
                                            onChange={(e) => setData('seo_booking_description', e.target.value)}
                                            placeholder={t('settings.publicPage.seoBookingDescriptionPlaceholder')}
                                            maxLength={160}
                                            rows={2}
                                        />
                                        <InputError message={errors.seo_booking_description} />
                                    </div>
                                </div>
                            </div>
                        </div>

                        {/* Display Options */}
                        <div className="space-y-4">
                            <h4 className="text-sm font-medium">
                                {t('settings.publicPage.optionsTitle')}
                            </h4>

                            <div className="flex items-center gap-3">
                                <Checkbox
                                    id="show_employees_section"
                                    checked={data.show_employees_section}
                                    onCheckedChange={(checked) =>
                                        setData('show_employees_section', !!checked)
                                    }
                                />
                                <Label htmlFor="show_employees_section" className="cursor-pointer">
                                    {t('settings.publicPage.showEmployeesSection')}
                                </Label>
                            </div>
                        </div>

                        {/* Submit */}
                        <div className="flex items-center gap-4">
                            <Button type="submit" disabled={processing}>
                                {t('settings.publicPage.save')}
                            </Button>

                            <Transition
                                show={recentlySuccessful}
                                enter="transition ease-in-out"
                                enterFrom="opacity-0"
                                leave="transition ease-in-out"
                                leaveTo="opacity-0"
                            >
                                <p className="text-sm text-neutral-600">
                                    {t('settings.publicPage.saved')}
                                </p>
                            </Transition>
                        </div>
                    </form>
                </div>
            </SettingsLayout>
        </AppLayout>
    );
}
