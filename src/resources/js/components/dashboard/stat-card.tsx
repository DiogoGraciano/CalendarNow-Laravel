import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { LucideIcon } from 'lucide-react';
import { cn } from '@/lib/utils';

interface StatCardProps {
    title: string;
    value: string | number;
    description: string;
    icon: LucideIcon;
    iconClassName?: string;
}

export function StatCard({ title, value, description, icon: Icon, iconClassName }: StatCardProps) {
    return (
        <Card>
            <CardHeader>
                <div className="flex items-center justify-between">
                    <div className="flex-1">
                        <CardTitle className="text-sm font-medium text-muted-foreground">
                            {title}
                        </CardTitle>
                        <CardDescription className="mt-2 text-2xl font-bold">
                            {value}
                        </CardDescription>
                    </div>
                    <div className={cn('rounded-lg bg-primary/10 p-3', iconClassName)}>
                        <Icon className="h-6 w-6 text-primary" />
                    </div>
                </div>
            </CardHeader>
            <CardContent>
                <CardDescription className="text-xs">
                    {description}
                </CardDescription>
            </CardContent>
        </Card>
    );
}

