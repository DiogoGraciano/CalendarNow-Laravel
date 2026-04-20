import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Bar, BarChart, CartesianGrid, Legend, ResponsiveContainer, XAxis, YAxis } from 'recharts';

interface DailyAppointmentsChartProps {
    data: Array<{ date: string; count: number }>;
}

export function DailyAppointmentsChart({ data }: DailyAppointmentsChartProps) {
    return (
        <Card>
            <CardHeader>
                <CardTitle>Agendamentos Diários</CardTitle>
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
                            allowDecimals={false}
                        />
                        <Legend />
                        <Bar 
                            dataKey="count" 
                            fill="var(--primary)" 
                            name="Agendamentos Diário"
                            radius={[4, 4, 0, 0]}
                        />
                    </BarChart>
                </ResponsiveContainer>
            </CardContent>
        </Card>
    );
}

