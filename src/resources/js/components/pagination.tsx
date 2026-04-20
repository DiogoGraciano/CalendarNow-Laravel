import { Link } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { ChevronLeft, ChevronRight } from 'lucide-react';
import { useTranslation } from '@/hooks/use-translation';

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface PaginationMeta {
    current_page: number;
    from: number | null;
    last_page: number;
    path: string;
    per_page: number;
    to: number | null;
    total: number;
}

interface PaginationLinks {
    first: string | null;
    last: string | null;
    prev: string | null;
    next: string | null;
}

interface PaginationProps {
    links: PaginationLink[];
    meta?: PaginationMeta;
    itemLabel?: string;
    showInfo?: boolean;
    className?: string;
}

export default function Pagination({
    links,
    meta,
    itemLabel = 'itens',
    showInfo = true,
    className = '',
}: PaginationProps) {
    const { t } = useTranslation();

    // Não renderizar se houver apenas uma página ou nenhum link
    if (!links || links.length === 0 || (meta && meta.last_page <= 1)) {
        return null;
    }

    // Encontrar links de primeira, anterior, próxima e última página
    // O Laravel pode retornar os labels com HTML entities ou texto simples
    const getLinkText = (label: string) => {
        // Remove HTML tags e decodifica entidades HTML
        const text = label.replace(/<[^>]*>/g, '').trim();
        return text.toLowerCase();
    };

    const firstLink = links.find((link) => {
        const text = getLinkText(link.label);
        return text.includes('&laquo;') || text.includes('«') || text.includes('first');
    });
    
    const prevLink = links.find((link) => {
        const text = getLinkText(link.label);
        return text.includes('previous') || text.includes('anterior') || text.includes('&lsaquo;');
    });
    
    const nextLink = links.find((link) => {
        const text = getLinkText(link.label);
        return text.includes('next') || text.includes('próxima') || text.includes('próximo') || text.includes('&rsaquo;');
    });
    
    const lastLink = links.find((link) => {
        const text = getLinkText(link.label);
        return text.includes('&raquo;') || text.includes('»') || text.includes('last');
    });
    
    // Links numéricos (páginas) - excluir links de navegação
    const pageLinks = links.filter((link) => {
        const text = getLinkText(link.label);
        return (
            !text.includes('&laquo;') &&
            !text.includes('«') &&
            !text.includes('&raquo;') &&
            !text.includes('»') &&
            !text.includes('previous') &&
            !text.includes('anterior') &&
            !text.includes('next') &&
            !text.includes('próxima') &&
            !text.includes('próximo') &&
            !text.includes('first') &&
            !text.includes('last') &&
            !text.includes('&lsaquo;') &&
            !text.includes('&rsaquo;')
        );
    });

    return (
        <div className={`flex flex-col sm:flex-row items-center justify-between gap-3 rounded-lg bg-white px-2 sm:px-4 py-3 shadow-sm dark:bg-neutral-900 ${className}`}>
            {/* Informações da paginação */}
            {showInfo && meta && (
                <div className="text-xs sm:text-sm text-neutral-500 dark:text-neutral-400 hidden sm:block">
                    {meta.from && meta.to ? (
                        <>
                            {t('pagination.showing', { 
                                from: meta.from, 
                                to: meta.to, 
                                total: meta.total,
                                itemLabel 
                            })}
                        </>
                    ) : (
                        <>
                            {t('pagination.total', { total: meta.total, itemLabel })}
                        </>
                    )}
                </div>
            )}

            {/* Controles de navegação */}
            <div className="flex items-center gap-1 sm:gap-2 w-full sm:w-auto justify-center sm:justify-start">
                {/* Primeira página */}
                {firstLink && firstLink.url && (
                    <Button
                        variant="outline"
                        size="sm"
                        asChild
                        className="hidden sm:flex"
                    >
                        <Link href={firstLink.url}>
                            <ChevronLeft className="h-4 w-4 mr-1" />
                            <ChevronLeft className="h-4 w-4" />
                        </Link>
                    </Button>
                )}

                {/* Página anterior */}
                {prevLink && prevLink.url && (
                    <Button
                        variant="outline"
                        size="sm"
                        asChild
                    >
                        <Link href={prevLink.url}>
                            <ChevronLeft className="h-4 w-4 sm:mr-1" />
                            <span className="hidden sm:inline">{t('pagination.previous')}</span>
                        </Link>
                    </Button>
                )}

                {/* Links de páginas numéricas */}
                <div className="flex items-center gap-1 sm:gap-2">
                    {pageLinks.map((link, index) => {
                        // Se o label contém "..." ou é muito longo, renderizar como texto
                        if (link.label.includes('...') || link.label.length > 3) {
                            return (
                                <span
                                    key={index}
                                    className="px-2 py-1 text-sm text-neutral-500 dark:text-neutral-400"
                                >
                                    {link.label.replace(/<[^>]*>/g, '')}
                                </span>
                            );
                        }

                        return link.url ? (
                            <Link
                                key={index}
                                href={link.url}
                                className={`px-3 py-1 text-sm rounded transition-colors ${
                                    link.active
                                        ? 'bg-primary text-primary-foreground font-semibold'
                                        : 'bg-muted hover:bg-muted/80 text-neutral-700 dark:text-neutral-300'
                                }`}
                                dangerouslySetInnerHTML={{ __html: link.label }}
                            />
                        ) : (
                            <span
                                key={index}
                                className={`px-3 py-1 text-sm rounded ${
                                    link.active
                                        ? 'bg-primary text-primary-foreground font-semibold'
                                        : 'bg-muted text-neutral-500 dark:text-neutral-400'
                                }`}
                                dangerouslySetInnerHTML={{ __html: link.label }}
                            />
                        );
                    })}
                </div>

                {/* Próxima página */}
                {nextLink && nextLink.url && (
                    <Button
                        variant="outline"
                        size="sm"
                        asChild
                    >
                        <Link href={nextLink.url}>
                            <span className="hidden sm:inline">{t('pagination.next')}</span>
                            <ChevronRight className="h-4 w-4 sm:ml-1" />
                        </Link>
                    </Button>
                )}

                {/* Última página */}
                {lastLink && lastLink.url && (
                    <Button
                        variant="outline"
                        size="sm"
                        asChild
                        className="hidden sm:flex"
                    >
                        <Link href={lastLink.url}>
                            <ChevronRight className="h-4 w-4" />
                            <ChevronRight className="h-4 w-4 ml-1" />
                        </Link>
                    </Button>
                )}

                {/* Indicador de página atual (mobile) */}
                {meta && (
                    <div className="text-xs text-neutral-500 dark:text-neutral-400 sm:hidden px-2">
                        {meta.current_page}/{meta.last_page}
                    </div>
                )}
            </div>
        </div>
    );
}

