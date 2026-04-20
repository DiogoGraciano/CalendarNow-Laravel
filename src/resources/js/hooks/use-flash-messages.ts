import { useEffect } from 'react';
import { usePage } from '@inertiajs/react';
import toast from 'react-hot-toast';

interface FlashMessages {
    flash?: {
        success?: string;
        error?: string;
        warning?: string;
        info?: string;
    };
}

export function useFlashMessages(): void {
    const { flash } = usePage<FlashMessages>().props;

    useEffect(() => {
        if (flash?.success) {
            toast.success(flash.success);
        }
        if (flash?.error) {
            toast.error(flash.error);
        }
        if (flash?.warning) {
            toast(flash.warning, {
                icon: '⚠️',
            });
        }
        if (flash?.info) {
            toast(flash.info, {
                icon: 'ℹ️',
            });
        }
    }, [flash]);
}

