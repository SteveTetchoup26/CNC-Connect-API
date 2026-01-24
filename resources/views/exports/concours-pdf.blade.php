<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Export des Concours</title>
    <style>
        @page {
            margin: 20px;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .header h1 {
            color: #2C3E50;
            margin: 0;
            font-size: 18px;
        }
        .header .date {
            color: #7F8C8D;
            font-size: 11px;
        }
        .filters {
            margin-bottom: 15px;
            padding: 10px;
            background: #F8F9FA;
            border-radius: 5px;
            font-size: 11px;
        }
        .filters strong {
            color: #2C3E50;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        table th {
            background-color: #2C3E50;
            color: white;
            text-align: center;
            padding: 8px;
            font-weight: bold;
            font-size: 11px;
        }
        table td {
            border: 1px solid #ddd;
            padding: 6px;
            font-size: 11px;
        }
        table tr:nth-child(even) {
            background-color: #F8F9FA;
        }
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 10px;
            color: #7F8C8D;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        .total {
            margin-top: 10px;
            padding: 8px;
            background-color: #2C3E50;
            color: white;
            text-align: center;
            font-weight: bold;
            font-size: 12px;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
    </style>
</head>
<body>
    <div class="header">
        <h1>LISTE DES CONCOURS</h1>
        <div class="date">
            Export généré le : {{ $filtres['date_export'] }}
        </div>
    </div>

    @if($filtres['search'] || $filtres['annee'] || $filtres['statut'])
        <div class="filters">
            <strong>Filtres appliqués :</strong>
            @if($filtres['search'])
                <br>Recherche : "{{ $filtres['search'] }}"
            @endif
            @if($filtres['annee'])
                <br>Année : {{ $filtres['annee'] }}
            @endif
            @if($filtres['statut'])
                <br>Statut : {{ $filtres['statut'] }}
            @endif
        </div>
    @endif

    <table>
        <thead>
            <tr>
                <th width="30%">Concours</th>
                <th width="15%">Session</th>
                <th width="12%">Date limite</th>
                <th width="12%">Date examen</th>
                <th width="15%">Frais (FCFA)</th>
                <th width="16%">Banque/Compte</th>
            </tr>
        </thead>
        <tbody>
            @foreach($concours as $concour)
                <tr>
                    <td>{{ $concour->libelle_concours }}</td>
                    <td class="text-center">{{ $concour->sessions->first()->libelle_session ?? 'N/A' }}</td>
                    <td class="text-center">{{ $concour->date_limite ? \Carbon\Carbon::parse($concour->date_limite)->format('d/m/Y') : 'N/A' }}</td>
                    <td class="text-center">{{ $concour->date_examen ? \Carbon\Carbon::parse($concour->date_examen)->format('d/m/Y') : 'N/A' }}</td>
                    <td class="text-right">
                        @if($concour->fraisConcours)
                            {{ number_format($concour->fraisConcours->montant_frais, 0, ',', ' ') }} FCFA
                        @else
                            0 FCFA
                        @endif
                    </td>
                    <td class="text-left">
                        @if($concour->fraisConcours)
                            <strong>{{ $concour->fraisConcours->nom_banque }}</strong><br>
                            <small>{{ $concour->fraisConcours->numero_compte }}</small>
                        @else
                            N/A
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="total">
        Total : {{ $filtres['total'] }} concours
    </div>

    <div class="footer">
        Plateforme de Gestion des Concours | Page @{{ $page }} sur @{{ $pages }}
    </div>
</body>
</html>
