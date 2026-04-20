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
import { DreForm, type DreFormData } from '@/components/forms/dre-form';

export interface DreOption {
    id: number;
    code: string;
    description?: string | null;
    type: 'receivable' | 'payable';
}

interface DreSelectionModalProps {
    isOpen: boolean;
    onOpenChange: (open: boolean) => void;
    selectedDreId: number | null;
    onSelectDre: (dre: DreOption | null) => void;
    dres: DreOption[];
    storeDreUrl: string;
    /** When true, show a "None" option at the top to clear selection */
    allowNone?: boolean;
}

function getCsrfToken(): string {
    const match = document.cookie.match(/XSRF-TOKEN=([^;]+)/);
    if (match) {
        return decodeURIComponent(match[1]);
    }
    return '';
}

function getDreDisplayLabel(dre: DreOption): string {
    if (dre.description?.trim()) {
        return `${dre.code} - ${dre.description}`;
    }
    return dre.code;
}

export function DreSelectionModal({
    isOpen,
    onOpenChange,
    selectedDreId,
    onSelectDre,
    dres,
    storeDreUrl,
    allowNone = false,
}: DreSelectionModalProps) {
    const { t } = useTranslation();
    const [searchTerm, setSearchTerm] = useState('');
    const [isCreateFormOpen, setIsCreateFormOpen] = useState(false);
    const [isSubmitting, setIsSubmitting] = useState(false);
    const [createError, setCreateError] = useState<string | null>(null);

    const filteredDres = useMemo(() => {
        if (!searchTerm.trim()) return dres;
        const term = searchTerm.toLowerCase().trim();
        return dres.filter(
            (d) =>
                d.code.toLowerCase().includes(term) ||
                (d.description?.toLowerCase().includes(term) ?? false)
        );
    }, [dres, searchTerm]);

    const handleSelect = (dre: DreOption) => {
        onSelectDre(dre);
        onOpenChange(false);
    };

    const handleCreateSubmit = async (data: DreFormData) => {
        setIsSubmitting(true);
        setCreateError(null);
        try {
            const res = await fetch(storeDreUrl, {
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
                    json?.errors?.code?.[0] ||
                    t('dre.selection.errorCreate');
                setCreateError(msg);
                return;
            }
            const newDre = json.dre as {
                id: number;
                code: string;
                description?: string;
                type: 'receivable' | 'payable';
            };
            handleSelect({
                id: newDre.id,
                code: newDre.code,
                description: newDre.description ?? null,
                type: newDre.type,
            });
        } catch {
            setCreateError(t('dre.selection.errorConnection'));
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
                    <DialogTitle>{t('dre.selection.title')}</DialogTitle>
                </DialogHeader>

                {!isCreateFormOpen ? (
                    <>
                        <div className="relative">
                            <Search className="absolute left-3 top-1/2 -translate-y-1/2 size-4 text-muted-foreground" />
                            <Input
                                type="text"
                                placeholder={t('dre.selection.searchPlaceholder')}
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
                                        onSelectDre(null);
                                        onOpenChange(false);
                                    }}
                                    className="w-full flex items-center justify-between gap-2 rounded-md border p-3 text-left transition-colors hover:bg-muted/50 focus:bg-muted/50 focus:outline-none focus:ring-2 focus:ring-ring"
                                >
                                    <span className="font-medium text-muted-foreground">
                                        {t('settings.tenant.none')}
                                    </span>
                                    {selectedDreId === null && (
                                        <Check className="size-4 shrink-0 text-primary" />
                                    )}
                                </button>
                            )}
                            {filteredDres.length === 0 ? (
                                <p className="text-sm text-muted-foreground p-4 text-center">
                                    {searchTerm.trim()
                                        ? t('dre.selection.noResults')
                                        : t('dre.selection.noDres')}
                                </p>
                            ) : (
                                filteredDres.map((dre) => (
                                    <button
                                        key={dre.id}
                                        type="button"
                                        onClick={() => handleSelect(dre)}
                                        className="w-full flex items-center justify-between gap-2 rounded-md border p-3 text-left transition-colors hover:bg-muted/50 focus:bg-muted/50 focus:outline-none focus:ring-2 focus:ring-ring"
                                    >
                                        <span className="font-medium">
                                            {getDreDisplayLabel(dre)}
                                        </span>
                                        {selectedDreId === dre.id && (
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
                            {t('dre.selection.createNew')}
                        </Button>

                        <DialogFooter>
                            <Button
                                type="button"
                                variant="outline"
                                onClick={() => handleClose(false)}
                            >
                                {t('dre.selection.cancel')}
                            </Button>
                        </DialogFooter>
                    </>
                ) : (
                    <>
                        <div className="py-2">
                            {createError && (
                                <p className="text-sm text-red-500 mb-2">
                                    {createError}
                                </p>
                            )}
                            <DreForm
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
