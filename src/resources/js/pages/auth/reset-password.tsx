import { update } from '@/routes/password';
import { Head, useForm } from '@inertiajs/react';

import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import AuthLayout from '@/layouts/auth-layout';
import { useTranslation } from '@/hooks/use-translation';
import { resetPasswordSchema } from '@/lib/validations';
import { validateWithYup } from '@/hooks/use-yup-validation';

interface ResetPasswordProps {
    token: string;
    email: string;
}

export default function ResetPassword({ token, email }: ResetPasswordProps) {
    const { t } = useTranslation();
    
    const { data, setData, post, processing, errors, clearErrors, setError } = useForm({
        email,
        token,
        password: '',
        password_confirmation: '',
    });

    const submit = async (e: React.FormEvent) => {
        e.preventDefault();
        
        // Validação client-side com Yup
        const yupErrors = await validateWithYup(resetPasswordSchema, data);
        if (yupErrors) {
            Object.keys(yupErrors).forEach((key) => {
                setError(key as keyof typeof data, yupErrors[key]);
            });
            return;
        }
        
        clearErrors();
        
        post(update.url(), {
            onSuccess: () => {
                setData('password', '');
                setData('password_confirmation', '');
            },
        });
    };
    
    return (
        <AuthLayout
            title={t('auth.resetPassword.title')}
            description={t('auth.resetPassword.description')}
        >
            <Head title={t('auth.resetPassword.pageTitle')} />

            <form onSubmit={submit}>
                <div className="grid gap-6">
                    <div className="grid gap-2">
                        <Label htmlFor="email">{t('auth.resetPassword.emailLabel')}</Label>
                        <Input
                            id="email"
                            type="email"
                            name="email"
                            autoComplete="email"
                            value={email}
                            className="mt-1 block w-full"
                            readOnly
                        />
                        <InputError
                            message={errors.email}
                            className="mt-2"
                        />
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor="password">{t('auth.resetPassword.passwordLabel')}</Label>
                        <Input
                            id="password"
                            type="password"
                            name="password"
                            autoComplete="new-password"
                            className="mt-1 block w-full"
                            autoFocus
                            value={data.password}
                            onChange={(e) => {
                                setData('password', e.target.value);
                                if (errors.password) {
                                    clearErrors('password');
                                }
                            }}
                            placeholder={t('auth.resetPassword.passwordPlaceholder')}
                        />
                        <InputError message={errors.password} />
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor="password_confirmation">
                            {t('auth.resetPassword.confirmPasswordLabel')}
                        </Label>
                        <Input
                            id="password_confirmation"
                            type="password"
                            name="password_confirmation"
                            autoComplete="new-password"
                            className="mt-1 block w-full"
                            value={data.password_confirmation}
                            onChange={(e) => {
                                setData('password_confirmation', e.target.value);
                                if (errors.password_confirmation) {
                                    clearErrors('password_confirmation');
                                }
                            }}
                            placeholder={t('auth.resetPassword.confirmPasswordPlaceholder')}
                        />
                        <InputError
                            message={errors.password_confirmation}
                            className="mt-2"
                        />
                    </div>

                    <Button
                        type="submit"
                        className="mt-4 w-full"
                        disabled={processing}
                        data-test="reset-password-button"
                    >
                        {processing && <Spinner />}
                        {t('auth.resetPassword.submit')}
                    </Button>
                </div>
            </form>
        </AuthLayout>
    );
}
