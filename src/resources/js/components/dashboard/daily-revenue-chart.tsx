import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { useFormatCurrency } from '@/hooks/use-format-currency';
import { Bar, BarChart, CartesianGrid, Legend, ResponsiveContainer, XAxis, YAxis } from 'recharts';

interface DailyRevenueChartProps {
    data: Array<{ date: string; total: number }>;
}

export function DailyRevenueChart({ data }: DailyRevenueChartProps) {
    const formatCurrency = useFormatCurrency();

    return (
        <Card>
            <CardHeader>
                <CardTitle>Faturamento Diário</CardTitle>
            </CardHeader>
            <CardContent>
                <ResponsiveContainer width="100%" height={300}>
                    <BarChart data={data}>
                        <CartesianGrid strokeDasharray="3 3" />
                        <XAxis 
                            dataKey="date" 
                            tick={{ fontSize: 12 }}
                            interval={1}
                        />
                        <YAxis 
                            tick={{ fontSize: 12 }}
                            tickFormatter={(value: number) => formatCurrency(value)}
                        />
                        <Legend />
                        <Bar 
                            dataKey="total" 
                            fill="var(--primary)" 
                            name="Faturamento Diário"
                            radius={[4, 4, 0, 0]}
                        />
                    </BarChart>
                </ResponsiveContainer>
            </CardContent>
        </Card>
    );
}

