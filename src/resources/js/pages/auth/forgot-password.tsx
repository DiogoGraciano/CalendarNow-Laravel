// Components
import { login } from '@/routes';
import { email } from '@/routes/password';
import { Head, useForm } from '@inertiajs/react';
import { LoaderCircle } from 'lucide-react';

import InputError from '@/components/input-error';
import TextLink from '@/components/text-link';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthLayout from '@/layouts/auth-layout';
import { useTranslation } from '@/hooks/use-translation';
import { forgotPasswordSchema } from '@/lib/validations';
import { validateWithYup } from '@/hooks/use-yup-validation';

export default function ForgotPassword({ status }: { status?: string }) {
    const { t } = useTranslation();
    
    const { data, setData, post, processing, errors, clearErrors, setError } = useForm({
        email: '',
    });

    const submit = async (e: React.FormEvent) => {
        e.preventDefault();
        
        // Validação client-side com Yup
        const yupErrors = await validateWithYup(forgotPasswordSchema, data);
        if (yupErrors) {
            Object.keys(yupErrors).forEach((key) => {
                setError(key as keyof typeof data, yupErrors[key]);
            });
            return;
        }
        
        clearErrors();
        
        post(email.url());
    };
    
    return (
        <AuthLayout
            title={t('auth.forgotPassword.title')}
            description={t('auth.forgotPassword.description')}
        >
            <Head title={t('auth.forgotPassword.pageTitle')} />

            {status && (
                <div className="mb-4 text-center text-sm font-medium text-green-600">
                    {status}
                </div>
            )}

            <div className="space-y-6">
                <form onSubmit={submit}>
                    <div className="grid gap-2">
                        <Label htmlFor="email">{t('auth.forgotPassword.emailLabel')}</Label>
                        <Input
                            id="email"
                            type="email"
                            name="email"
                            autoComplete="off"
                            autoFocus
                            value={data.email}
                            onChange={(e) => {
                                setData('email', e.target.value);
                                if (errors.email) {
                                    clearErrors('email');
                                }
                            }}
                            placeholder={t('auth.forgotPassword.emailPlaceholder')}
                        />

                        <InputError message={errors.email} />
                    </div>

                    <div className="my-6 flex items-center justify-start">
                        <Button
                            type="submit"
                            className="w-full"
                            disabled={processing}
                            data-test="email-password-reset-link-button"
                        >
                            {processing && (
                                <LoaderCircle className="h-4 w-4 animate-spin" />
                            )}
                            {t('auth.forgotPassword.submit')}
                        </Button>
                    </div>
                </form>

                <div className="space-x-1 text-center text-sm text-muted-foreground">
                    <span>{t('auth.forgotPassword.orReturn')}</span>
                    <TextLink href={login()}>{t('auth.forgotPassword.logIn')}</TextLink>
                </div>
            </div>
        </AuthLayout>
    );
}
