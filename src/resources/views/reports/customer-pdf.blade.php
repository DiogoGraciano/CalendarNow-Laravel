<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Relatório de Clientes</title>
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

        .new-vs-returning {
            margin-bottom: 16px;
            padding: 8px 12px;
            background: #e3f2fd;
            border-radius: 4px;
            display: flex;
            gap: 20px;
        }

        .new-vs-returning .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 10px;
            font-weight: bold;
        }

        .badge-new {
            background: #c8e6c9;
            color: #2e7d32;
        }

        .badge-returning {
            background: #bbdefb;
            color: #1565c0;
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
        <h1>Relatório de Clientes</h1>
        <p>Data de Geração: {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    <div class="filters">
        <p><strong>Filtros aplicados:</strong></p>
        <p>Data Início: {{ $filters['dt_ini'] ? \Carbon\Carbon::parse($filters['dt_ini'])->format('d/m/Y H:i') : '-' }}</p>
        <p>Data Fim: {{ $filters['dt_fim'] ? \Carbon\Carbon::parse($filters['dt_fim'])->format('d/m/Y H:i') : '-' }}</p>
        <p>Mínimo de Visitas: {{ $filters['min_visits'] ?? '-' }}</p>
    </div>

    <div class="totals">
        <p>Total de Clientes: {{ $summary['total_customers'] }}</p>
        <p>Total de Visitas: {{ $summary['total_visits'] }}</p>
        @if($summary['avg_ticket'] > 0)
            <p>Ticket Médio: R$ {{ number_format($summary['avg_ticket'], 2, ',', '.') }}</p>
        @endif
        <p class="total-geral">Receita Total: R$ {{ number_format($summary['total_revenue'], 2, ',', '.') }}</p>
    </div>

    <div class="new-vs-returning">
        <p><span class="badge badge-new">Novos Clientes: {{ $newVsReturning['new_customers'] }}</span></p>
        <p><span class="badge badge-returning">Clientes Recorrentes: {{ $newVsReturning['returning_customers'] }}</span></p>
    </div>

    @if(count($customers) > 0)
        <table>
            <thead>
                <tr>
                    <th>Cliente</th>
                    <th>Email</th>
                    <th>Telefone</th>
                    <th class="text-center">Visitas</th>
                    <th class="text-right">Total Gasto</th>
                    <th class="text-right">Ticket Médio</th>
                    <th>Última Visita</th>
                </tr>
            </thead>
            <tbody>
                @foreach($customers as $customer)
                    <tr>
                        <td>{{ $customer['customer_name'] }}</td>
                        <td>{{ $customer['customer_email'] ?? '-' }}</td>
                        <td>{{ $customer['customer_phone'] ?? '-' }}</td>
                        <td class="text-center">{{ $customer['visit_count'] }}</td>
                        <td class="text-right">R$ {{ number_format($customer['total_spent'], 2, ',', '.') }}</td>
                        <td class="text-right">R$ {{ number_format($customer['avg_ticket'], 2, ',', '.') }}</td>
                        <td>{{ $customer['last_visit'] ? \Carbon\Carbon::parse($customer['last_visit'])->format('d/m/Y H:i') : '-' }}</td>
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
