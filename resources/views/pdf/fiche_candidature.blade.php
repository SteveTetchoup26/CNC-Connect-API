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
            font-family: 'Arial', 'Helvetica', sans-serif;
            padding: 10px;
            background: white;
            font-size: 9.5pt;
            line-height: 1.3;
        }

        .main-border {
            border: 4px solid #000;
            padding: 0;
        }

        .inner-border {
            border: 2px solid #000;
            margin: 6px;
            padding: 15px;
        }

        /* En-tête tripartite */
        .header-section {
            display: table;
            width: 100%;
            border-bottom: 3px solid #000;
            padding-bottom: 10px;
            margin-bottom: 10px;
        }

        .header-left, .header-center, .header-right {
            display: table-cell;
            vertical-align: top;
            padding: 0 8px;
        }

        .header-left {
            width: 35%;
            text-align: left;
            font-size: 8pt;
        }

        .header-center {
            width: 30%;
            text-align: center;
            vertical-align: middle;
        }

        .header-right {
            width: 35%;
            text-align: right;
            font-size: 8pt;
        }

        .header-left p, .header-right p {
            margin: 1px 0;
            line-height: 1.2;
        }

        .country-motto {
            font-weight: bold;
            font-size: 8.5pt;
            margin-bottom: 2px;
        }

        .school-name {
            font-weight: bold;
            font-size: 9pt;
            margin: 4px 0;
        }

        .school-details {
            font-size: 7.5pt;
            color: #333;
        }

        .logo {
            max-width: 100px;
            max-height: 100px;
            height: auto;
        }

        /* Titre principal avec QR Code */
        .main-title {
            text-align: center;
            background: white;
            border: 2px solid #000;
            padding: 10px;
            margin: 12px 0;
            position: relative;
        }

        .main-title h1 {
            font-size: 11pt;
            font-weight: bold;
            margin: 0 40px 4px 0;
            text-transform: uppercase;
        }

        .main-title h2 {
            font-size: 12pt;
            font-weight: bold;
            color: #0066cc;
            margin: 0;
            text-transform: uppercase;
        }

        .qr-code-in-title {
            position: absolute;
            top: 5px;
            right: 5px;
            width: 70px;
            height: 70px;
            border: 1px solid #000;
            padding: 2px;
        }

        .qr-code {
            width: 100%;
            height: 100%;
        }

        /* Section code inscription */
        .inscription-section {
            display: table;
            width: 100%;
            margin: 10px 0;
        }

        .inscription-left {
            display: table-cell;
            width: 70%;
            vertical-align: middle;
        }

        .inscription-right {
            display: table-cell;
            width: 30%;
            text-align: right;
            vertical-align: middle;
            font-size: 8pt;
            font-style: italic;
        }

        .code-inscription {
            font-size: 11pt;
            font-weight: bold;
        }

        .code-number {
            color: #dc3545;
            font-size: 12pt;
        }

        /* Sections avec en-tête noir */
        .section-header {
            background: #000;
            color: white;
            padding: 6px 10px;
            margin: 10px 0 0 0;
            font-weight: bold;
            font-size: 9pt;
            text-transform: uppercase;
        }

        .section-content {
            border: 1px solid #000;
            border-top: none;
            padding: 8px;
            margin-bottom: 10px;
            font-size: 9pt;
        }

        /* Disposition horizontale des infos */
        .info-line {
            margin: 3px 0;
            line-height: 1.4;
        }

        .info-label {
            font-weight: bold;
            display: inline;
        }

        .info-value {
            display: inline;
        }

        .info-separator {
            margin: 0 15px;
        }

        /* Documents section (red header) */
        .section-header-red {
            background: white;
            color: #dc3545;
            padding: 6px 10px;
            margin: 10px 0 0 0;
            font-weight: bold;
            font-size: 9pt;
            text-transform: uppercase;
            border: 1px solid #dc3545;
            border-bottom: 3px solid #dc3545;
        }

        .documents-list {
            border: 1px solid #dc3545;
            border-top: none;
            padding: 8px;
            margin-bottom: 10px;
            font-size: 8pt;
            line-height: 1.5;
        }

        .documents-list p {
            margin: 5px 0;
        }

        /* Footer */
        .footer-section {
            display: table;
            width: 100%;
            margin-top: 15px;
            padding-top: 10px;
            border-top: 2px solid #000;
        }

        .footer-left {
            display: table-cell;
            width: 50%;
            text-align: left;
            font-weight: bold;
            font-size: 10pt;
        }

        .footer-right {
            display: table-cell;
            width: 50%;
            text-align: right;
            font-style: italic;
            font-size: 9pt;
        }

        .code-candidat {
            color: #dc3545;
            font-size: 11pt;
        }
    </style>
</head>
<body>
    <div class="main-border">
        <div class="inner-border">

            <!-- En-tête tripartite -->
            <div class="header-section">
                <!-- Partie gauche (Français) -->
                <div class="header-left">
                    <p class="country-motto">RÉPUBLIQUE DU CAMEROUN</p>
                    <p style="font-style: italic; font-size: 7.5pt;">Paix - Travail - Patrie</p>
                    <p class="school-name">{{ strtoupper($ecole->libelle_ecole ?? 'ÉCOLE') }}</p>
                    @if($ecole)
                    <p class="school-details">BP: {{ $ecole->bp_ecole }}</p>
                    <p class="school-details">Tél: {{ $ecole->telephone_ecole }}</p>
                    <p class="school-details">E-Mail: {{ $ecole->email_ecole }}</p>
                    <p class="school-details">Site Web: {{ $ecole->siteweb_ecole }}</p>
                    @endif
                </div>

                <!-- Partie centrale (Logo) -->
                <div class="header-center">
                    @if($ecole && $ecole->logo_url)
                        <img src="{{ public_path('storage/' . $ecole->logo_url) }}" alt="Logo" class="logo">
                    @endif
                    @if($ecole && $ecole->embleme_ecole)
                        <p style="font-size: 7pt; margin-top: 4px; font-style: italic;">{{ $ecole->embleme_ecole }}</p>
                    @endif
                </div>

                <!-- Partie droite (Anglais) -->
                <div class="header-right">
                    <p class="country-motto">REPUBLIC OF CAMEROON</p>
                    <p style="font-style: italic; font-size: 7.5pt;">Peace - Work - Fatherland</p>
                    <p class="school-name">{{ strtoupper($ecole->libelle_ecole ?? 'SCHOOL') }}</p>
                    @if($ecole)
                    <p class="school-details">PO-Box: {{ $ecole->bp_ecole }}</p>
                    <p class="school-details">Phone: {{ $ecole->telephone_ecole }}</p>
                    <p class="school-details">E-Mail: {{ $ecole->email_ecole }}</p>
                    <p class="school-details">Site Web: {{ $ecole->siteweb_ecole }}</p>
                    @endif
                </div>
            </div>

            <!-- Titre principal avec QR Code -->
            <div class="main-title">
                @if($qrCodeImage)
                <div class="qr-code-in-title">
                    <img src="{{ $qrCodeImage }}" alt="QR Code" class="qr-code">
                </div>
                @endif
                <h1>Fiche d'inscription au concours d'entrée {{ strtoupper($ecole->libelle_ecole ?? '') }}</h1>
                <h2>Session {{ $concours->date_examen->format('Y') }}</h2>
            </div>

            <!-- Code d'inscription -->
            <div class="inscription-section">
                <div class="inscription-left">
                    @if($candidature->code_candidat_def)
                        <span class="code-inscription">CODE CANDIDAT N° <span class="code-number">{{ $candidature->code_candidat_def }}</span></span>
                    @else
                        <span class="code-inscription">INSCRIPTION N° <span class="code-number">{{ $candidature->code_candidat_temp }}</span></span>
                    @endif
                </div>
                <div class="inscription-right">
                    <em>Timbre Fiscal ici /<br>Stamp here</em>
                </div>
            </div>

            <!-- Informations Personnelles -->
            <div class="section-header">Informations Personnelles / Personal Informations</div>
            <div class="section-content">
                <div class="info-line">
                    <span class="info-label">Nom:</span> <span class="info-value">{{ strtoupper($utilisateur->nom) }}</span>
                    <span class="info-separator"></span>
                    <span class="info-label">Prénom:</span> <span class="info-value">{{ strtoupper($utilisateur->prenom) }}</span>
                </div>

                <div class="info-line">
                    <span class="info-label">Date naissance:</span> <span class="info-value">{{ \Carbon\Carbon::parse($candidat->date_naissance)->format('Y-m-d') }}</span>
                    <span class="info-separator"></span>
                    <span class="info-label">Lieu de naissance:</span> <span class="info-value">{{ strtoupper($candidat->lieu_naissance) }}</span>
                    <span class="info-separator"></span>
                    <span class="info-label">Sexe:</span> <span class="info-value">{{ $candidat->sexe == 'M' ? 'Masculin' : 'Féminin' }}</span>
                </div>

                <div class="info-line">
                    <span class="info-label">Nationalité:</span> <span class="info-value">{{ $candidat->nationalite }}</span>
                    <span class="info-separator"></span>
                    <span class="info-label">Région d'origine:</span> <span class="info-value">{{ $candidat->region_origine }}</span>
                    <span class="info-separator"></span>
                    <span class="info-label">Département d'origine:</span> <span class="info-value">{{ $candidat->departement_origine }}</span>
                </div>

                <div class="info-line">
                    <span class="info-label">CNI:</span> <span class="info-value">{{ $candidat->numero_cni }}</span>
                    <span class="info-separator"></span>
                    <span class="info-label">Téléphone:</span> <span class="info-value">{{ $utilisateur->telephone ?? 'N/A' }}</span>
                    <span class="info-separator"></span>
                    <span class="info-label">Adresse:</span> <span class="info-value">{{ $candidat->adresse }}</span>
                </div>

                <div class="info-line">
                    <span class="info-label">1ère Langue:</span> <span class="info-value">Français</span>
                    <span class="info-separator"></span>
                    <span class="info-label">Email:</span> <span class="info-value">{{ $utilisateur->email }}</span>
                </div>
            </div>

            <!-- Informations Académiques -->
            <div class="section-header">Informations Académique / Academic Informations</div>
            <div class="section-content">
                <div class="info-line">
                    <span class="info-label">Diplôme d'admission:</span> <span class="info-value">{{ $candidat->diplome_admission }}</span>
                </div>

                <div class="info-line">
                    <span class="info-label">Filière:</span> <span class="info-value">{{ strtoupper($candidat->filiere_origine) }}</span>
                    <span class="info-separator"></span>
                    <span class="info-label">Niveau:</span> <span class="info-value">{{ $candidat->niveau_scolaire }}</span>
                    <span class="info-separator"></span>
                    <span class="info-label">Mention:</span> <span class="info-value">{{ strtoupper($candidat->mention ?? 'N/A') }}</span>
                </div>

                <div class="info-line">
                    <span class="info-label">Année diplôme:</span> <span class="info-value">{{ $candidat->annee_diplome }}</span>
                    <span class="info-separator"></span>
                    <span class="info-label">Centre d'examen:</span> <span class="info-value">{{ strtoupper($centreExamen->ville_centre) }}</span>
                    <span class="info-separator"></span>
                    <span class="info-label">Centre de dépôt:</span> <span class="info-value">{{ strtoupper($centreDepot->ville_centre) }}</span>
                </div>
            </div>

            <!-- Autres Informations -->
            <div class="section-header">Autres Informations / Others Informations</div>
            <div class="section-content">
                <div class="info-line">
                    <span class="info-label">Nom du père:</span> <span class="info-value">{{ strtoupper($candidat->nom_pere ?? 'N/A') }}</span>
                    <span class="info-separator"></span>
                    <span class="info-label">Téléphone du père:</span> <span class="info-value">{{ $candidat->telephone_pere ?? 'N/A' }}</span>
                </div>

                <div class="info-line">
                    <span class="info-label">Nom de la mère:</span> <span class="info-value">{{ strtoupper($candidat->nom_mere) }}</span>
                    <span class="info-separator"></span>
                    <span class="info-label">Téléphone de la mère:</span> <span class="info-value">{{ $candidat->telephone_mere }}</span>
                </div>
            </div>

            <!-- Documents Nécessaires (Dynamique) -->
            @if($specification)
            <div class="section-header-red">Documents Nécessaires / Necessary Documents</div>
            <div class="documents-list">
                @if($specification->acte_naissance)
                <p>• Une photocopie certifiée d'acte de naissance datant de moins de trois (03) mois. <em>/ A certified true photocopy of the birth certificate issued within the last three (03) months.</em></p>
                @endif

                @if($specification->diplomes)
                <p>• Une photocopie certifiée conforme du diplôme/attestation requis. <em>/ A certified true copy of the required diploma.</em></p>
                @endif

                @if($specification->releve_notes)
                <p>• Un relevé de notes officiel. <em>/ An official transcript of records.</em></p>
                @endif

                @if($specification->certificat_nationalite)
                <p>• Un certificat de nationalité. <em>/ A certificate of nationality.</em></p>
                @endif

                @if($specification->carte_nationale_identite)
                <p>• Une photocopie de la carte nationale d'identité. <em>/ A photocopy of the national identity card.</em></p>
                @endif

                @if($specification->photo)
                <p>• Quatre (04) photos d'identité 4x4 du candidat. <em>/ Four (04) 4x4 identity photos of the candidate.</em></p>
                @endif

                <p>• Un certificat médical signé par un médecin fonctionnaire, datant de moins de trois (03) mois et certifiant que le candidat est apte à poursuivre des études supérieures. <em>/ A medical certificate issued within the last three (03) months by a state medical practitioner, and testifying that the candidate is fit for higher education.</em></p>

                <p>• Un reçu de versement bancaire d'un montant de {{ number_format($concours->frais_inscription, 0, ',', ' ') }} FCFA. <em>/ A bank deposit receipt of {{ number_format($concours->frais_inscription, 0, ',', ' ') }} CFA francs.</em></p>

                <p>• Une enveloppe A4 timbrée au tarif intérieur réglementaire et portant l'adresse exacte du candidat. <em>/ A 21 x 29.7 size self-addressed envelope bearing a 400 CFA francs postal stamp.</em></p>

                @if($specification->desc_infos_concours)
                <p style="margin-top: 8px; font-style: italic;">{{ $specification->desc_infos_concours }}</p>
                @endif
            </div>
            @endif

            <!-- Footer avec code candidat -->
            <div class="footer-section">
                <div class="footer-left">
                    Code Candidat: <span class="code-candidat">{{ $candidature->code_candidat_def ?? $candidature->code_candidat_temp }}</span>
                </div>
                <div class="footer-right">
                    Imprimée le {{ now()->format('d/m/Y') }}
                </div>
            </div>

        </div>
    </div>
</body>
</html>
