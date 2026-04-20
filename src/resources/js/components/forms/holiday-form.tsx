import { useForm } from '@inertiajs/react';
import * as yup from 'yup';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Button } from '@/components/ui/button';
import { DialogFooter } from '@/components/ui/dialog';
import { Textarea } from '@/components/ui/textarea';
import { useTranslation } from '@/hooks/use-translation';
import { useState } from 'react';

interface Holiday {
    id: number;
    name: string;
    date: string;
    recurring: boolean;
    notes?: string;
}

interface HolidayFormData {
    name: string;
    date: string;
    recurring: boolean;
    notes?: string;
}

interface HolidayFormProps {
    holiday?: Holiday | null;
    onSubmit: (formData: HolidayFormData) => void;
    onCancel: () => void;
}

const holidaySchema = yup.object().shape({
    name: yup.string().required('Nome é obrigatório'),
    date: yup.string().required('Data é obrigatória'),
    recurring: yup.boolean(),
    notes: yup.string().nullable(),
});

export function HolidayForm({ holiday, onSubmit, onCancel }: HolidayFormProps) {
    const { t } = useTranslation();

    const { data, setData, errors, processing, clearErrors } = useForm<HolidayFormData>({
        name: holiday?.name || '',
        date: holiday?.date ? holiday.date.substring(0, 10) : '',
        recurring: holiday?.recurring || false,
        notes: holiday?.notes || '',
    });

    const [validationErrors, setValidationErrors] = useState<Record<string, string>>({});

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        clearErrors();
        setValidationErrors({});

        try {
            const formData: HolidayFormData = {
                name: data.name,
                date: data.date,
                recurring: data.recurring,
                notes: data.notes || undefined,
            };

            await holidaySchema.validate(formData, { abortEarly: false });
            onSubmit(formData);
        } catch (err) {
            if (err instanceof yup.ValidationError) {
                const yupErrors: Record<string, string> = {};
                err.inner.forEach((error) => {
                    if (error.path) {
                        yupErrors[error.path] = error.message;
                    }
                });
                setValidationErrors(yupErrors);
            }
        }
    };

    const getError = (field: string) => {
        return errors[field as keyof typeof errors] || validationErrors[field] || null;
    };

    return (
        <form onSubmit={handleSubmit}>
            <div className="grid gap-4 py-4">
                <div className="grid grid-cols-2 gap-4">
                    <div className="space-y-2">
                        <Label htmlFor="name">
                            {t('holiday.form.name')} <span className="text-red-500">*</span>
                        </Label>
                        <Input
                            id="name"
                            value={data.name}
                            onChange={(e) => setData('name', e.target.value)}
                            placeholder={t('holiday.form.namePlaceholder')}
                            className={getError('name') ? 'border-red-500' : ''}
                        />
                        {getError('name') && (
                            <p className="text-sm text-red-500">{getError('name')}</p>
                        )}
                    </div>

                    <div className="space-y-2">
                        <Label htmlFor="date">
                            {t('holiday.form.date')} <span className="text-red-500">*</span>
                        </Label>
                        <Input
                            id="date"
                            type="date"
                            value={data.date}
                            onChange={(e) => setData('date', e.target.value)}
                            className={getError('date') ? 'border-red-500' : ''}
                        />
                        {getError('date') && (
                            <p className="text-sm text-red-500">{getError('date')}</p>
                        )}
                    </div>
                </div>

                <div className="flex items-center gap-2">
                    <input
                        id="recurring"
                        type="checkbox"
                        checked={data.recurring}
                        onChange={(e) => setData('recurring', e.target.checked)}
                        className="h-4 w-4 rounded border-neutral-300"
                    />
                    <Label htmlFor="recurring">{t('holiday.form.recurring')}</Label>
                </div>

                <div className="space-y-2">
                    <Label htmlFor="notes">{t('holiday.form.notes')}</Label>
                    <Textarea
                        id="notes"
                        value={data.notes || ''}
                        onChange={(e) => setData('notes', e.target.value)}
                        placeholder={t('holiday.form.notesPlaceholder')}
                        rows={3}
                    />
                </div>
            </div>
            <DialogFooter>
                <Button type="button" variant="outline" onClick={onCancel} disabled={processing}>
                    {t('common.cancel')}
                </Button>
                <Button type="submit" disabled={processing} className="bg-green-600 hover:bg-green-700">
                    {holiday ? t('common.update') : t('common.create')} {t('holiday.form.holiday')}
                </Button>
            </DialogFooter>
        </form>
    );
}
