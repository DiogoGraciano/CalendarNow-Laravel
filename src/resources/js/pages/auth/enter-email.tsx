import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import AuthLayout from '@/layouts/auth-layout';
import { useTranslation } from '@/hooks/use-translation';
import { enterEmailSchema } from '@/lib/validations';
import { validateWithYup } from '@/hooks/use-yup-validation';
import { Head, useForm } from '@inertiajs/react';

export default function EnterEmail() {
    const { t } = useTranslation();

    const { data, setData, post, processing, errors, clearErrors, setError } = useForm({
        email: '',
    });

    const submit = async (e: React.FormEvent) => {
        e.preventDefault();

        const yupErrors = await validateWithYup(enterEmailSchema, data);
        if (yupErrors) {
            Object.keys(yupErrors).forEach((key) => {
                setError(key as keyof typeof data, yupErrors[key]);
            });
            return;
        }

        clearErrors();

        post('/login/redirect', {
            preserveScroll: true,
        });
    };

    return (
        <AuthLayout
            title={t('auth.enterEmail.title')}
            description={t('auth.enterEmail.description')}
        >
            <Head title={t('auth.enterEmail.pageTitle')} />

            <form onSubmit={submit} className="flex flex-col gap-6">
                <div className="grid gap-2">
                    <Label htmlFor="email">{t('auth.enterEmail.emailLabel')}</Label>
                    <Input
                        id="email"
                        type="email"
                        name="email"
                        required
                        autoFocus
                        autoComplete="email"
                        value={data.email}
                        onChange={(e) => {
                            setData('email', e.target.value);
                            if (errors.email) {
                                clearErrors('email');
                            }
                        }}
                        placeholder={t('auth.enterEmail.emailPlaceholder')}
                    />
                    <InputError
                        message={
                            errors.email === 'auth.throttle'
                                ? t('auth.errors.throttle', { seconds: 60 })
                                : errors.email
                        }
                    />
                </div>

                <Button
                    type="submit"
                    className="w-full"
                    disabled={processing}
                    data-test="enter-email-continue-button"
                >
                    {processing && <Spinner />}
                    {t('auth.enterEmail.submit')}
                </Button>
            </form>
        </AuthLayout>
    );
}
