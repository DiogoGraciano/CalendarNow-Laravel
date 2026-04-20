import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { CartesianGrid, Legend, Line, LineChart, ResponsiveContainer, XAxis, YAxis } from 'recharts';

interface TrendLineChartProps {
    data: Array<{ date: string; count: number }>;
}

export function TrendLineChart({ data }: TrendLineChartProps) {
    return (
        <Card>
            <CardHeader>
                <CardTitle>Tendência de Agendamentos</CardTitle>
            </CardHeader>
            <CardContent>
                <ResponsiveContainer width="100%" height={300}>
                    <LineChart data={data}>
                        <CartesianGrid strokeDasharray="3 3" />
                        <XAxis 
                            dataKey="date" 
                            tick={{ fontSize: 12 }}
                            interval={2}
                        />
                        <YAxis 
                            tick={{ fontSize: 12 }}
                            allowDecimals={false}
                        />
                        <Legend />
                        <Line 
                            type="monotone" 
                            dataKey="count" 
                            stroke="var(--primary)" 
                            strokeWidth={2}
                            strokeDasharray="5 5"
                            dot={{ r: 4 }}
                            name="Agendamentos"
                        />
                    </LineChart>
                </ResponsiveContainer>
            </CardContent>
        </Card>
    );
}

