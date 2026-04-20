import { useState } from 'react';
import AppLayout from '@/layouts/app-layout';
import { Head, useForm } from '@inertiajs/react';
import { useTranslation } from '@/hooks/use-translation';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { DreSelectionModal } from '@/components/selection-modals/dre-selection-modal';
import type { DreOption } from '@/components/selection-modals/dre-selection-modal';
import StoreDreAction from '@/actions/App/Actions/Dre/StoreDreAction';
import { type BreadcrumbItem } from '@/types';
import { index as configuracoesIndex, update as configuracoesUpdate } from '@/routes/configuracoes';
import SettingsLayout from '@/layouts/settings/layout';
import HeadingSmall from '@/components/heading-small';

interface SettingsTenantProps {
    dres: DreOption[];
    schedulingDefaultDreId: number | null;
}

function getDreDisplayLabel(dre: DreOption): string {
    if (dre.description?.trim()) {
        return `${dre.code} - ${dre.description}`;
    }
    return dre.code;
}

export default function SettingsTenant({
    dres,
    schedulingDefaultDreId,
}: SettingsTenantProps) {
    const { t } = useTranslation();
    const [isDreSelectionOpen, setIsDreSelectionOpen] = useState(false);
    const [dresList, setDresList] = useState<DreOption[]>(dres);
    const { data, setData, put, processing, errors } = useForm({
        scheduling_default_dre_id: schedulingDefaultDreId?.toString() ?? '',
    });

    const selectedDreId = data.scheduling_default_dre_id
        ? parseInt(data.scheduling_default_dre_id, 10)
        : null;
    const selectedDre =
        selectedDreId != null && !Number.isNaN(selectedDreId)
            ? dresList.find((d) => d.id === selectedDreId) ?? null
            : null;

    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: t('settings.tenant.title'),
            href: configuracoesIndex().url,
        },
    ];

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        put(configuracoesUpdate.url());
    };

    const handleSelectDre = (dre: DreOption | null) => {
        setData('scheduling_default_dre_id', dre ? dre.id.toString() : '');
        if (dre) {
            setDresList((prev) => (prev.some((d) => d.id === dre.id) ? prev : [...prev, dre]));
        }
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={t('settings.tenant.title')} />
            <SettingsLayout>
                <div className="space-y-6">
                    <HeadingSmall
                        title={t('settings.tenant.title')}
                        description={t('settings.tenant.schedulingDefaultDreHelp')}
                    />

                    <form onSubmit={handleSubmit} className="max-w-md space-y-6">
                        <div className="space-y-2">
                            <Label htmlFor="scheduling_default_dre_id">
                                {t('settings.tenant.schedulingDefaultDre')}
                            </Label>
                            <Button
                                id="scheduling_default_dre_id"
                                type="button"
                                variant="outline"
                                className="w-full justify-start font-normal h-9"
                                onClick={() => setIsDreSelectionOpen(true)}
                            >
                                {selectedDre
                                    ? getDreDisplayLabel(selectedDre)
                                    : t('settings.tenant.none')}
                            </Button>
                            {errors.scheduling_default_dre_id && (
                                <p className="text-sm text-destructive">
                                    {errors.scheduling_default_dre_id}
                                </p>
                            )}
                        </div>
                        <Button type="submit" disabled={processing}>
                            {processing ? t('common.saving') : t('common.save')}
                        </Button>
                    </form>

                    <DreSelectionModal
                        isOpen={isDreSelectionOpen}
                        onOpenChange={setIsDreSelectionOpen}
                        selectedDreId={Number.isNaN(selectedDreId) ? null : selectedDreId}
                        onSelectDre={handleSelectDre}
                        dres={dresList}
                        storeDreUrl={StoreDreAction.url()}
                        allowNone
                    />
                </div>
            </SettingsLayout>
        </AppLayout>
    );
}
