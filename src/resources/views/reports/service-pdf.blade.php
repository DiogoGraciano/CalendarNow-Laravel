<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Relatório de Serviços</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11px;
            color: #1a1a1a;
            line-height: 1.4;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #333;
        }

        .header h1 {
            font-size: 18px;
            margin-bottom: 4px;
        }

        .header p {
            font-size: 11px;
            color: #555;
        }

        .filters {
            margin-bottom: 16px;
            padding: 8px 12px;
            background: #f5f5f5;
            border-radius: 4px;
        }

        .filters p {
            font-size: 10px;
            color: #555;
            margin-bottom: 2px;
        }

        .totals {
            margin-bottom: 16px;
            padding: 10px 12px;
            background: #e8f5e9;
            border-radius: 4px;
        }

        .totals p {
            margin-bottom: 2px;
        }

        .totals .total-geral {
            font-size: 14px;
            font-weight: bold;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 4px;
        }

        table th {
            background: #f5f5f5;
            padding: 4px 6px;
            text-align: left;
            font-size: 9px;
            text-transform: uppercase;
            color: #666;
            border-bottom: 1px solid #ddd;
        }

        table th.text-right,
        table td.text-right {
            text-align: right;
        }

        table th.text-center,
        table td.text-center {
            text-align: center;
        }

        table td {
            padding: 4px 6px;
            border-bottom: 1px solid #eee;
            font-size: 10px;
        }

        table tr:nth-child(even) {
            background: #fafafa;
        }

        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 9px;
            color: #999;
        }
    </style>
</head>
<body>
    <div class="header">
        @if(!empty($tenantName))
            <h1>{{ $tenantName }}</h1>
        @endif
        <h1>Relatório de Serviços</h1>
        <p>Data de Geração: {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    <div class="filters">
        <p><strong>Filtros aplicados:</strong></p>
        <p>Agenda: {{ $filters['calendar'] ? 'ID ' . $filters['calendar'] : 'Todas' }}</p>
        <p>Funcionário: {{ $filters['employee'] ? 'ID ' . $filters['employee'] : 'Todos' }}</p>
        <p>Data Início: {{ $filters['dt_ini'] ? \Carbon\Carbon::parse($filters['dt_ini'])->format('d/m/Y H:i') : '-' }}</p>
        <p>Data Fim: {{ $filters['dt_fim'] ? \Carbon\Carbon::parse($filters['dt_fim'])->format('d/m/Y H:i') : '-' }}</p>
    </div>

    <div class="totals">
        <p>Total de Serviços: {{ $summary['total_services'] }}</p>
        <p>Total de Agendamentos: {{ $summary['total_bookings'] }}</p>
        @if($summary['avg_price'] > 0)
            <p>Preço Médio: R$ {{ number_format($summary['avg_price'], 2, ',', '.') }}</p>
        @endif
        <p class="total-geral">Receita Total: R$ {{ number_format($summary['total_revenue'], 2, ',', '.') }}</p>
    </div>

    @if(count($services) > 0)
        <table>
            <thead>
                <tr>
                    <th>Serviço</th>
                    <th class="text-right">Preço Catálogo</th>
                    <th class="text-center">Vezes Agendado</th>
                    <th class="text-right">Receita Total</th>
                    <th class="text-right">Preço Médio</th>
                    <th class="text-right">Desconto</th>
                    <th class="text-center">Participação (%)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($services as $service)
                    <tr>
                        <td>{{ $service['service_name'] }}</td>
                        <td class="text-right">R$ {{ number_format($service['catalog_price'], 2, ',', '.') }}</td>
                        <td class="text-center">{{ $service['times_booked'] }}</td>
                        <td class="text-right">R$ {{ number_format($service['total_revenue'], 2, ',', '.') }}</td>
                        <td class="text-right">R$ {{ number_format($service['avg_price'], 2, ',', '.') }}</td>
                        <td class="text-right">R$ {{ number_format($service['total_discount'], 2, ',', '.') }}</td>
                        <td class="text-center">{{ $service['revenue_share'] }}%</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p style="text-align: center; padding: 20px; color: #999;">Nenhum dado encontrado para os filtros selecionados.</p>
    @endif

    <div class="footer">
        <p>Relatório gerado automaticamente pelo CalendarNow</p>
    </div>
</body>
</html>
