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

export interface AccountOption {
    id: number;
    name: string;
    code?: string | null;
}

interface AccountSelectionModalProps {
    isOpen: boolean;
    onOpenChange: (open: boolean) => void;
    selectedAccountId: number | null;
    onSelectAccount: (account: AccountOption | null) => void;
    accounts: AccountOption[];
    /** When true, show a "None" option at the top to clear selection */
    allowNone?: boolean;
}

function getAccountDisplayLabel(account: AccountOption): string {
    if (account.code?.trim()) {
        return `${account.name} (${account.code})`;
    }
    return account.name;
}

export function AccountSelectionModal({
    isOpen,
    onOpenChange,
    selectedAccountId,
    onSelectAccount,
    accounts,
    allowNone = false,
}: AccountSelectionModalProps) {
    const { t } = useTranslation();
    const [searchTerm, setSearchTerm] = useState('');

    const filteredAccounts = useMemo(() => {
        if (!searchTerm.trim()) return accounts;
        const term = searchTerm.toLowerCase().trim();
        return accounts.filter(
            (a) =>
                a.name.toLowerCase().includes(term) ||
                (a.code?.toLowerCase().includes(term) ?? false)
        );
    }, [accounts, searchTerm]);

    const handleSelect = (account: AccountOption) => {
        onSelectAccount(account);
        onOpenChange(false);
    };

    const handleClose = (open: boolean) => {
        if (!open) {
            setSearchTerm('');
        }
        onOpenChange(open);
    };

    return (
        <Dialog open={isOpen} onOpenChange={handleClose}>
            <DialogContent className="max-w-md max-h-[90vh] flex flex-col">
                <DialogHeader>
                    <DialogTitle>{t('account.selection.title')}</DialogTitle>
                </DialogHeader>

                <div className="relative">
                    <Search className="absolute left-3 top-1/2 -translate-y-1/2 size-4 text-muted-foreground" />
                    <Input
                        type="text"
                        placeholder={t('account.selection.searchPlaceholder')}
                        value={searchTerm}
                        onChange={(e) => setSearchTerm(e.target.value)}
                        className="pl-9"
                    />
                </div>

                <div className="min-h-[200px] max-h-[300px] overflow-y-auto rounded-md border space-y-1 p-1">
                    {allowNone && (
                        <button
                            type="button"
                            onClick={() => {
                                onSelectAccount(null);
                                onOpenChange(false);
                            }}
                            className="w-full flex items-center justify-between gap-2 rounded-md border p-3 text-left transition-colors hover:bg-muted/50 focus:bg-muted/50 focus:outline-none focus:ring-2 focus:ring-ring"
                        >
                            <span className="font-medium text-muted-foreground">
                                {t('settings.tenant.none')}
                            </span>
                            {selectedAccountId === null && (
                                <Check className="size-4 shrink-0 text-primary" />
                            )}
                        </button>
                    )}
                    {filteredAccounts.length === 0 ? (
                        <p className="text-sm text-muted-foreground p-4 text-center">
                            {searchTerm.trim()
                                ? t('account.selection.noResults')
                                : t('account.selection.noAccounts')}
                        </p>
                    ) : (
                        filteredAccounts.map((account) => (
                            <button
                                key={account.id}
                                type="button"
                                onClick={() => handleSelect(account)}
                                className="w-full flex items-center justify-between gap-2 rounded-md border p-3 text-left transition-colors hover:bg-muted/50 focus:bg-muted/50 focus:outline-none focus:ring-2 focus:ring-ring"
                            >
                                <span className="font-medium">
                                    {getAccountDisplayLabel(account)}
                                </span>
                                {selectedAccountId === account.id && (
                                    <Check className="size-4 shrink-0 text-primary" />
                                )}
                            </button>
                        ))
                    )}
                </div>

                <DialogFooter>
                    <Button
                        type="button"
                        variant="outline"
                        onClick={() => handleClose(false)}
                    >
                        {t('account.selection.cancel')}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}
