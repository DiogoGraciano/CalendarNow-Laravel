<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Relatório DRE</title>
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

        .dre-group {
            margin-bottom: 16px;
            page-break-inside: avoid;
        }

        .dre-header {
            padding: 6px 10px;
            background: #e0e0e0;
            font-weight: bold;
            font-size: 12px;
            border-radius: 4px 4px 0 0;
            display: flex;
            justify-content: space-between;
        }

        .dre-header .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 10px;
            font-weight: bold;
        }

        .badge-receivable {
            background: #c8e6c9;
            color: #2e7d32;
        }

        .badge-payable {
            background: #ffcdd2;
            color: #c62828;
        }

        .badge-none {
            background: #e0e0e0;
            color: #424242;
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

        table td {
            padding: 4px 6px;
            border-bottom: 1px solid #eee;
            font-size: 10px;
        }

        table tr:nth-child(even) {
            background: #fafafa;
        }

        .subtotal {
            text-align: right;
            font-weight: bold;
            padding: 4px 6px;
            font-size: 11px;
            background: #f0f0f0;
            border-radius: 0 0 4px 4px;
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
        <h1>Demonstração do Resultado do Exercício (DRE)</h1>
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
        <p>Total de Agendamentos: {{ $totals['total_agendamentos'] }}</p>
        @if($totals['ticket_medio'] > 0)
            <p>Ticket Médio: R$ {{ number_format($totals['ticket_medio'], 2, ',', '.') }}</p>
        @endif
        <p class="total-geral">Total Geral: R$ {{ number_format($totals['total_geral'], 2, ',', '.') }}</p>
    </div>

    @forelse($schedulingsByDre as $group)
        <div class="dre-group">
            <div class="dre-header">
                <span>
                    <span class="badge {{ $group['dre'] ? ($group['dre']['type'] === 'receivable' ? 'badge-receivable' : 'badge-payable') : 'badge-none' }}">
                        {{ $group['dre_code'] }}
                    </span>
                    {{ $group['dre_description'] }}
                </span>
                <span>{{ count($group['schedulings']) }} agendamentos</span>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Cliente</th>
                        <th>Agenda</th>
                        <th>Funcionário</th>
                        <th>Data Início</th>
                        <th>Data Fim</th>
                        <th class="text-right">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($group['schedulings'] as $scheduling)
                        <tr>
                            <td>{{ $scheduling['id'] }}</td>
                            <td>{{ $scheduling['customer']['name'] ?? '-' }}</td>
                            <td>{{ $scheduling['calendar']['name'] ?? '-' }}</td>
                            <td>{{ $scheduling['employee']['name'] ?? '-' }}</td>
                            <td>{{ \Carbon\Carbon::parse($scheduling['start_time'])->format('d/m/Y H:i') }}</td>
                            <td>{{ \Carbon\Carbon::parse($scheduling['end_time'])->format('d/m/Y H:i') }}</td>
                            <td class="text-right">R$ {{ number_format($scheduling['total'], 2, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="subtotal">
                Subtotal: R$ {{ number_format($group['total'], 2, ',', '.') }}
            </div>
        </div>
    @empty
        <p style="text-align: center; padding: 20px; color: #999;">Nenhum dado encontrado para os filtros selecionados.</p>
    @endforelse

    <div class="footer">
        <p>Relatório gerado automaticamente pelo CalendarNow</p>
    </div>
</body>
</html>
