import { useState } from 'react';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Button } from '@/components/ui/button';
import { DialogFooter } from '@/components/ui/dialog';
import { useTranslation } from '@/hooks/use-translation';

export interface CustomerFormData {
    name: string;
    email: string;
    phone: string;
}

interface CustomerFormProps {
    onSubmit: (data: CustomerFormData) => void;
    onCancel: () => void;
    processing?: boolean;
}

export function CustomerForm({ onSubmit, onCancel, processing = false }: CustomerFormProps) {
    const { t } = useTranslation();
    const [name, setName] = useState('');
    const [email, setEmail] = useState('');
    const [phone, setPhone] = useState('');
    const [error, setError] = useState<string | null>(null);

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        setError(null);
        const trimmedName = name.trim();
        if (!trimmedName) {
            setError(t('customer.form.nameRequired'));
            return;
        }
        onSubmit({
            name: trimmedName,
            email: email.trim() || '',
            phone: phone.trim() || '',
        });
    };

    return (
        <form onSubmit={handleSubmit}>
            <div className="grid gap-4 py-4">
                <div className="space-y-2">
                    <Label htmlFor="customer-name">
                        {t('customer.form.nameLabel')} <span className="text-red-500">*</span>
                    </Label>
                    <Input
                        id="customer-name"
                        value={name}
                        onChange={(e) => setName(e.target.value)}
                        placeholder={t('customer.form.namePlaceholder')}
                        className={error ? 'border-red-500' : ''}
                    />
                </div>
                <div className="space-y-2">
                    <Label htmlFor="customer-email">{t('customer.form.emailLabel')}</Label>
                    <Input
                        id="customer-email"
                        type="email"
                        value={email}
                        onChange={(e) => setEmail(e.target.value)}
                        placeholder={t('customer.form.emailPlaceholder')}
                    />
                </div>
                <div className="space-y-2">
                    <Label htmlFor="customer-phone">{t('customer.form.phoneLabel')}</Label>
                    <Input
                        id="customer-phone"
                        value={phone}
                        onChange={(e) => setPhone(e.target.value)}
                        placeholder={t('customer.form.phonePlaceholder')}
                    />
                </div>
                {error && <p className="text-sm text-red-500">{error}</p>}
            </div>
            <DialogFooter>
                <Button type="button" variant="outline" onClick={onCancel} disabled={processing}>
                    {t('customer.form.cancel')}
                </Button>
                <Button type="submit" disabled={processing}>
                    {t('customer.form.submit')}
                </Button>
            </DialogFooter>
        </form>
    );
}
