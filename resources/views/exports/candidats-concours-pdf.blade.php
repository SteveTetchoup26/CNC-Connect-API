<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des candidats - {{ $concours->libelle_concours }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            margin: 20px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #4472C4;
            padding-bottom: 15px;
        }
        .header h1 {
            color: #4472C4;
            font-size: 20px;
            margin: 0 0 10px 0;
        }
        .header h2 {
            color: #666;
            font-size: 16px;
            margin: 5px 0;
            font-weight: normal;
        }
        .info-section {
            margin-bottom: 20px;
            background: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
        }
        .info-section table {
            width: 100%;
        }
        .info-section td {
            padding: 5px;
        }
        .info-section td:first-child {
            font-weight: bold;
            width: 150px;
        }
        table.candidats {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table.candidats th {
            background-color: #4472C4;
            color: white;
            padding: 10px 5px;
            text-align: left;
            font-weight: bold;
            border: 1px solid #2C5AA0;
        }
        table.candidats td {
            padding: 8px 5px;
            border: 1px solid #ddd;
        }
        table.candidats tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        table.candidats tr:hover {
            background-color: #e9ecef;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 9px;
            font-weight: bold;
        }
        .badge-attente {
            background-color: #ffc107;
            color: #000;
        }
        .badge-approuvee {
            background-color: #28a745;
            color: #fff;
        }
        .badge-rejetee {
            background-color: #dc3545;
            color: #fff;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>LISTE DES CANDIDATS</h1>
        <h2>{{ $concours->libelle_concours }}</h2>
    </div>

    <div class="info-section">
        <table>
            <tr>
                <td>École :</td>
                <td>{{ $ecole }}</td>
                <td>Session :</td>
                <td>{{ $session }}</td>
            </tr>
            <tr>
                <td>Date limite :</td>
                <td>{{ \Carbon\Carbon::parse($concours->date_limite)->format('d/m/Y') }}</td>
                <td>Date d'examen :</td>
                <td>{{ \Carbon\Carbon::parse($concours->date_examen)->format('d/m/Y') }}</td>
            </tr>
            <tr>
                <td>Total candidats :</td>
                <td><strong>{{ $total_candidats }}</strong></td>
                <td>Date d'export :</td>
                <td>{{ $date_export }}</td>
            </tr>
        </table>
    </div>

    <table class="candidats">
        <thead>
            <tr>
                <th style="width: 5%;">N°</th>
                <th style="width: 15%;">Nom</th>
                <th style="width: 15%;">Prénom</th>
                <th style="width: 20%;">Email</th>
                <th style="width: 10%;">Sexe</th>
                <th style="width: 15%;">N° CNI</th>
                <th style="width: 20%;">Centre d'examen</th>
            </tr>
        </thead>
        <tbody>
            @forelse($candidats as $candidat)
                <tr>
                    <td style="text-align: center;">{{ $candidat['numero'] }}</td>
                    <td>{{ $candidat['nom'] }}</td>
                    <td>{{ $candidat['prenom'] }}</td>
                    <td style="font-size: 10px;">{{ $candidat['email'] }}</td>
                    <td>{{ $candidat['sexe'] }}</td>
                    <td>{{ $candidat['numero_cni'] }}</td>
                    <td style="font-size: 10px;">{{ $candidat['centre_examen'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align: center; padding: 20px; color: #999;">
                        Aucun candidat trouvé pour ce concours
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Document généré automatiquement le {{ $date_export }}
    </div>
</body>
</html>
