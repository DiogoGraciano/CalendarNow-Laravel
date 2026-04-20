import { useState, useMemo } from 'react';
import { Search, Check } from 'lucide-react';
import { useTranslation } from '@/hooks/use-translation';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogFooter,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { CustomerForm, type CustomerFormData } from '@/components/forms/customer-form';

export interface CustomerOption {
    id: number;
    name: string;
    email?: string | null;
    phone?: string | null;
}

interface CustomerSelectionModalProps {
    isOpen: boolean;
    onOpenChange: (open: boolean) => void;
    selectedCustomerId: number | null;
    onSelectCustomer: (customer: CustomerOption) => void;
    customers: CustomerOption[];
    storeCustomerUrl: string;
}

function getCsrfToken(): string {
    const match = document.cookie.match(/XSRF-TOKEN=([^;]+)/);
    if (match) {
        return decodeURIComponent(match[1]);
    }
    return '';
}

export function CustomerSelectionModal({
    isOpen,
    onOpenChange,
    selectedCustomerId,
    onSelectCustomer,
    customers,
    storeCustomerUrl,
}: CustomerSelectionModalProps) {
    const { t } = useTranslation();
    const [searchTerm, setSearchTerm] = useState('');
    const [isCreateFormOpen, setIsCreateFormOpen] = useState(false);
    const [isSubmitting, setIsSubmitting] = useState(false);
    const [createError, setCreateError] = useState<string | null>(null);

    const filteredCustomers = useMemo(() => {
        if (!searchTerm.trim()) return customers;
        const term = searchTerm.toLowerCase().trim();
        return customers.filter((c) => c.name.toLowerCase().includes(term));
    }, [customers, searchTerm]);

    const handleSelect = (customer: CustomerOption) => {
        onSelectCustomer(customer);
        onOpenChange(false);
    };

    const handleCreateSubmit = async (data: CustomerFormData) => {
        setIsSubmitting(true);
        setCreateError(null);
        try {
            const res = await fetch(storeCustomerUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-XSRF-TOKEN': getCsrfToken(),
                },
                credentials: 'same-origin',
                body: JSON.stringify(data),
            });
            const json = await res.json();
            if (!res.ok) {
                const msg =
                    json?.message ||
                    json?.errors?.name?.[0] ||
                    t('customer.selection.errorCreate');
                setCreateError(msg);
                return;
            }
            const newCustomer = json.customer as { id: number; name: string; email?: string; phone?: string };
            handleSelect({
                id: newCustomer.id,
                name: newCustomer.name,
                email: newCustomer.email ?? null,
                phone: newCustomer.phone ?? null,
            });
        } catch {
            setCreateError(t('customer.selection.errorConnection'));
        } finally {
            setIsSubmitting(false);
        }
    };

    const handleClose = (open: boolean) => {
        if (!open) {
            setIsCreateFormOpen(false);
            setSearchTerm('');
            setCreateError(null);
        }
        onOpenChange(open);
    };

    return (
        <Dialog open={isOpen} onOpenChange={handleClose}>
            <DialogContent className="max-w-md max-h-[90vh] flex flex-col">
                <DialogHeader>
                    <DialogTitle>{t('customer.selection.title')}</DialogTitle>
                </DialogHeader>

                {!isCreateFormOpen ? (
                    <>
                        <div className="relative">
                            <Search className="absolute left-3 top-1/2 -translate-y-1/2 size-4 text-muted-foreground" />
                            <Input
                                type="text"
                                placeholder={t('customer.selection.searchPlaceholder')}
                                value={searchTerm}
                                onChange={(e) => setSearchTerm(e.target.value)}
                                className="pl-9"
                            />
                        </div>

                        <div className="min-h-[200px] max-h-[300px] overflow-y-auto rounded-md border space-y-1 p-1">
                            {filteredCustomers.length === 0 ? (
                                <p className="text-sm text-muted-foreground p-4 text-center">
                                    {searchTerm.trim()
                                        ? t('customer.selection.noResults')
                                        : t('customer.selection.noCustomers')}
                                </p>
                            ) : (
                                filteredCustomers.map((customer) => (
                                    <button
                                        key={customer.id}
                                        type="button"
                                        onClick={() => handleSelect(customer)}
                                        className="w-full flex items-center justify-between gap-2 rounded-md border p-3 text-left transition-colors hover:bg-muted/50 focus:bg-muted/50 focus:outline-none focus:ring-2 focus:ring-ring"
                                    >
                                        <span className="font-medium">{customer.name}</span>
                                        {selectedCustomerId === customer.id && (
                                            <Check className="size-4 shrink-0 text-primary" />
                                        )}
                                    </button>
                                ))
                            )}
                        </div>

                        <Button
                            type="button"
                            variant="outline"
                            className="w-full"
                            onClick={() => setIsCreateFormOpen(true)}
                        >
                            {t('customer.selection.createNew')}
                        </Button>

                        <DialogFooter>
                            <Button
                                type="button"
                                variant="outline"
                                onClick={() => handleClose(false)}
                            >
                                {t('customer.selection.cancel')}
                            </Button>
                        </DialogFooter>
                    </>
                ) : (
                    <>
                        <div className="py-2">
                            {createError && (
                                <p className="text-sm text-red-500 mb-2">{createError}</p>
                            )}
                            <CustomerForm
                                onSubmit={handleCreateSubmit}
                                onCancel={() => {
                                    setIsCreateFormOpen(false);
                                    setCreateError(null);
                                }}
                                processing={isSubmitting}
                            />
                        </div>
                    </>
                )}
            </DialogContent>
        </Dialog>
    );
}
