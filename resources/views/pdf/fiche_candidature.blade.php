<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fiche de Candidature</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            padding: 20px;
            background: white;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            border: 2px solid #333;
            padding: 30px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 3px solid #333;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }

        .logo {
            max-width: 120px;
            height: auto;
        }

        .school-info {
            text-align: center;
            flex: 1;
            margin: 0 20px;
        }

        .school-info h1 {
            font-size: 20px;
            color: #333;
            margin-bottom: 5px;
        }

        .school-info p {
            font-size: 12px;
            color: #666;
            margin: 2px 0;
        }

        .qr-code {
            width: 100px;
            height: 100px;
        }

        .title {
            text-align: center;
            background: #333;
            color: white;
            padding: 15px;
            margin: 20px 0;
            font-size: 18px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .code-section {
            text-align: center;
            background: #f0f0f0;
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
        }

        .code-section .code {
            font-size: 24px;
            font-weight: bold;
            color: #d9534f;
            letter-spacing: 2px;
        }

        .info-grid {
            display: table;
            width: 100%;
            margin: 20px 0;
        }

        .info-row {
            display: table-row;
        }

        .info-label {
            display: table-cell;
            padding: 10px;
            font-weight: bold;
            border: 1px solid #ddd;
            background: #f9f9f9;
            width: 35%;
        }

        .info-value {
            display: table-cell;
            padding: 10px;
            border: 1px solid #ddd;
        }

        .section-title {
            background: #666;
            color: white;
            padding: 10px;
            margin: 20px 0 10px 0;
            font-weight: bold;
            font-size: 14px;
        }

        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 2px solid #333;
            text-align: center;
            font-size: 12px;
            color: #666;
        }

        .important-note {
            background: #fff3cd;
            border: 1px solid #ffc107;
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
        }

        .important-note strong {
            color: #856404;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            @if($ecole && $ecole->logo_url)
                <img src="{{ public_path('storage/' . $ecole->logo_url) }}" alt="Logo" class="logo">
            @else
                <div style="width: 120px;"></div>
            @endif

            <div class="school-info">
                <h1>{{ $ecole->libelle_ecole ?? 'ÉCOLE' }}</h1>
                @if($ecole)
                    <p>{{ $ecole->localisation }}</p>
                    <p>BP: {{ $ecole->bp_ecole }} | Tél: {{ $ecole->telephone_ecole }}</p>
                    <p>Email: {{ $ecole->email_ecole }} | Site: {{ $ecole->siteweb_ecole }}</p>
                    @if($ecole->devise)
                        <p><em>"{{ $ecole->devise }}"</em></p>
                    @endif
                @endif
            </div>

            @if($qrCodeImage)
                <img src="{{ $qrCodeImage }}" alt="QR Code" class="qr-code">
            @endif
        </div>

        <!-- Title -->
        <div class="title">
            Fiche de Candidature
        </div>

        <!-- Code Temporaire -->
        <div class="code-section">
            <p style="margin-bottom: 5px; font-size: 14px;">Code Candidat Temporaire</p>
            <div class="code">{{ $candidature->code_candidat_temp }}</div>
        </div>

        <!-- Informations du Concours -->
        <div class="section-title">INFORMATIONS DU CONCOURS</div>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-label">Concours</div>
                <div class="info-value">{{ $concours->libelle_concours }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Date d'examen</div>
                <div class="info-value">{{ $concours->date_examen->format('d/m/Y à H:i') }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Date limite d'inscription</div>
                <div class="info-value">{{ $concours->date_limite->format('d/m/Y à H:i') }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Frais d'inscription</div>
                <div class="info-value">{{ number_format($concours->frais_inscription, 0, ',', ' ') }} FCFA</div>
            </div>
        </div>

        <!-- Informations du Candidat -->
        <div class="section-title">INFORMATIONS DU CANDIDAT</div>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-label">Nom complet</div>
                <div class="info-value">{{ $utilisateur->nom }} {{ $utilisateur->prenom }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Date de naissance</div>
                <div class="info-value">{{ \Carbon\Carbon::parse($candidat->date_naissance)->format('d/m/Y') }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Lieu de naissance</div>
                <div class="info-value">{{ $candidat->lieu_naissance }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Sexe</div>
                <div class="info-value">{{ $candidat->sexe == 'M' ? 'Masculin' : 'Féminin' }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">CNI</div>
                <div class="info-value">{{ $candidat->numero_cni }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Nationalité</div>
                <div class="info-value">{{ $candidat->nationalite }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Email</div>
                <div class="info-value">{{ $utilisateur->email }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Téléphone</div>
                <div class="info-value">{{ $utilisateur->telephone ?? 'N/A' }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Adresse</div>
                <div class="info-value">{{ $candidat->adresse }}</div>
            </div>
        </div>

        <!-- Informations Scolaires -->
        <div class="section-title">INFORMATIONS SCOLAIRES</div>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-label">Niveau scolaire</div>
                <div class="info-value">{{ $candidat->niveau_scolaire }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Filière d'origine</div>
                <div class="info-value">{{ $candidat->filiere_origine }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Diplôme</div>
                <div class="info-value">{{ $candidat->diplome_admission }}</div>
            </div>
            @if($candidat->mention)
            <div class="info-row">
                <div class="info-label">Mention</div>
                <div class="info-value">{{ $candidat->mention }}</div>
            </div>
            @endif
            <div class="info-row">
                <div class="info-label">Année du diplôme</div>
                <div class="info-value">{{ $candidat->annee_diplome }}</div>
            </div>
        </div>

        <!-- Centres -->
        <div class="section-title">CENTRES</div>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-label">Centre de dépôt</div>
                <div class="info-value">{{ $centreDepot->libelle_centre }} - {{ $centreDepot->ville_centre }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Centre d'examen</div>
                <div class="info-value">{{ $centreExamen->libelle_centre }} - {{ $centreExamen->ville_centre }}</div>
            </div>
        </div>

        <!-- Note importante -->
        <div class="important-note">
            <strong>IMPORTANT :</strong> Cette fiche est provisoire. Vous devez déposer votre dossier physique complet
            au centre de dépôt avant le <strong>{{ $concours->date_limite->format('d/m/Y') }}</strong>.
            Après validation, vous recevrez votre code candidat définitif.
        </div>

        <!-- Footer -->
        <div class="footer">
            <p><strong>Date d'inscription :</strong> {{ $candidature->date_inscription }}</p>
            <p>Scanez le QR Code ci-dessus pour télécharger cette fiche</p>
            <p style="margin-top: 10px;">
                <em>{{ $ecole->devise ?? '' }}</em>
            </p>
        </div>
    </div>
</body>
</html>
