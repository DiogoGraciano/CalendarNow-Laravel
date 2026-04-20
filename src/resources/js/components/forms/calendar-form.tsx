import { useForm } from '@inertiajs/react';
import * as yup from 'yup';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Button } from '@/components/ui/button';
import { DialogFooter } from '@/components/ui/dialog';
import { useTranslation } from '@/hooks/use-translation';
import { useState } from 'react';

interface Calendar {
    id: number;
    name: string;
    code?: string;
}

interface CalendarFormData {
    name: string;
}

interface CalendarFormProps {
    calendar?: Calendar | null;
    onSubmit: (formData: CalendarFormData) => void;
    onCancel: () => void;
}

const calendarSchema = yup.object().shape({
    name: yup.string().required('Nome é obrigatório'),
});

export function CalendarForm({ calendar, onSubmit, onCancel }: CalendarFormProps) {
    const { t } = useTranslation();
    const { data, setData, errors, processing, clearErrors } = useForm<CalendarFormData>({
        name: calendar?.name || '',
    });

    const [validationErrors, setValidationErrors] = useState<Record<string, string>>({});

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        clearErrors();
        setValidationErrors({});

        try {
            const formData: CalendarFormData = {
                name: data.name,
            };

            await calendarSchema.validate(formData, { abortEarly: false });
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
                <div className="space-y-2">
                    <Label htmlFor="name">
                        {t('calendar.form.name')} <span className="text-red-500">*</span>
                    </Label>
                    <Input
                        id="name"
                        value={data.name}
                        onChange={(e) => setData('name', e.target.value)}
                        placeholder={t('calendar.form.namePlaceholder')}
                        className={getError('name') ? 'border-red-500' : ''}
                    />
                    {getError('name') && (
                        <p className="text-sm text-red-500">{getError('name')}</p>
                    )}
                </div>
            </div>
            <DialogFooter>
                <Button
                    type="button"
                    variant="outline"
                    onClick={onCancel}
                    disabled={processing}
                >
                    {t('common.cancel')}
                </Button>
                <Button type="submit" disabled={processing} className="bg-green-600 hover:bg-green-700">
                    {calendar ? t('common.update') : t('common.create')} {t('calendar.form.calendar')}
                </Button>
            </DialogFooter>
        </form>
    );
}

