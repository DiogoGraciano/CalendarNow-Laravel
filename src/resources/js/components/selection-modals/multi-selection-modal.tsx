import { useState, useMemo } from 'react';
import { Search, Check } from 'lucide-react';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogFooter,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Label } from '@/components/ui/label';
import { useTranslation } from '@/hooks/use-translation';

export interface SelectableOption {
    id: number;
    name: string;
}

interface MultiSelectionModalProps {
    isOpen: boolean;
    onOpenChange: (open: boolean) => void;
    title: string;
    options: SelectableOption[];
    selectedIds: number[];
    onConfirm: (selectedIds: number[]) => void;
    searchPlaceholder?: string;
    emptyMessage?: string;
}

export function MultiSelectionModal({
    isOpen,
    onOpenChange,
    title,
    options,
    selectedIds,
    onConfirm,
    searchPlaceholder,
    emptyMessage,
}: MultiSelectionModalProps) {
    const { t } = useTranslation();
    const [searchTerm, setSearchTerm] = useState('');
    const [localSelectedIds, setLocalSelectedIds] = useState<number[]>(selectedIds);

    const filteredOptions = useMemo(() => {
        if (!searchTerm.trim()) return options;
        const term = searchTerm.toLowerCase().trim();
        return options.filter((o) => o.name.toLowerCase().includes(term));
    }, [options, searchTerm]);

    const handleToggle = (id: number) => {
        setLocalSelectedIds((prev) =>
            prev.includes(id) ? prev.filter((i) => i !== id) : [...prev, id],
        );
    };

    const handleSelectAll = () => {
        const allFilteredIds = filteredOptions.map((o) => o.id);
        const allSelected = allFilteredIds.every((id) => localSelectedIds.includes(id));
        if (allSelected) {
            setLocalSelectedIds((prev) => prev.filter((id) => !allFilteredIds.includes(id)));
        } else {
            setLocalSelectedIds((prev) => [...new Set([...prev, ...allFilteredIds])]);
        }
    };

    const handleConfirm = () => {
        onConfirm(localSelectedIds);
        onOpenChange(false);
    };

    const handleClose = (open: boolean) => {
        if (!open) {
            setSearchTerm('');
            setLocalSelectedIds(selectedIds);
        }
        onOpenChange(open);
    };

    const handleOpen = () => {
        setLocalSelectedIds(selectedIds);
    };

    const allFilteredSelected = filteredOptions.length > 0 && filteredOptions.every((o) => localSelectedIds.includes(o.id));

    return (
        <Dialog
            open={isOpen}
            onOpenChange={(open) => {
                if (open) handleOpen();
                handleClose(open);
            }}
        >
            <DialogContent className="max-w-md max-h-[90vh] flex flex-col">
                <DialogHeader>
                    <DialogTitle>{title}</DialogTitle>
                </DialogHeader>

                <div className="relative">
                    <Search className="absolute left-3 top-1/2 -translate-y-1/2 size-4 text-muted-foreground" />
                    <Input
                        type="text"
                        placeholder={searchPlaceholder ?? t('common.search')}
                        value={searchTerm}
                        onChange={(e) => setSearchTerm(e.target.value)}
                        className="pl-9"
                    />
                </div>

                {filteredOptions.length > 0 && (
                    <div className="flex items-center gap-2 px-1">
                        <Checkbox
                            id="select-all"
                            checked={allFilteredSelected}
                            onCheckedChange={handleSelectAll}
                        />
                        <Label htmlFor="select-all" className="cursor-pointer text-sm text-muted-foreground">
                            {t('common.selectAll')} ({localSelectedIds.length}/{options.length})
                        </Label>
                    </div>
                )}

                <div className="min-h-[200px] max-h-[300px] overflow-y-auto rounded-md border space-y-1 p-1">
                    {filteredOptions.length === 0 ? (
                        <p className="text-sm text-muted-foreground p-4 text-center">
                            {emptyMessage ?? t('common.noResults')}
                        </p>
                    ) : (
                        filteredOptions.map((option) => (
                            <button
                                key={option.id}
                                type="button"
                                onClick={() => handleToggle(option.id)}
                                className="w-full flex items-center justify-between gap-2 rounded-md border p-3 text-left transition-colors hover:bg-muted/50 focus:bg-muted/50 focus:outline-none focus:ring-2 focus:ring-ring"
                            >
                                <span className="font-medium">{option.name}</span>
                                {localSelectedIds.includes(option.id) && (
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
                        {t('common.cancel')}
                    </Button>
                    <Button
                        type="button"
                        onClick={handleConfirm}
                        className="bg-green-600 hover:bg-green-700"
                    >
                        {t('common.confirm')} ({localSelectedIds.length})
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}
