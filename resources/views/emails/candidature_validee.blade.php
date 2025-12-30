<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Candidature Validée</title>
</head>
<body style="margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f4f4f4;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f4f4f4; padding: 20px;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    <!-- Header -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); padding: 40px 30px; text-align: center;">
                            <h1 style="color: #ffffff; margin: 0; font-size: 32px;">✅ Félicitations !</h1>
                            <p style="color: #ffffff; margin: 10px 0 0 0; font-size: 18px; font-weight: bold;">Votre candidature a été validée</p>
                        </td>
                    </tr>

                    <!-- Content -->
                    <tr>
                        <td style="padding: 40px 30px;">
                            <p style="color: #333333; font-size: 16px; line-height: 1.6; margin: 0 0 20px 0;">
                                Bonjour <strong>{{ $candidat->utilisateur->nom }} {{ $candidat->utilisateur->prenom }}</strong>,
                            </p>

                            <p style="color: #555555; font-size: 15px; line-height: 1.6; margin: 0 0 25px 0;">
                                Nous avons le plaisir de vous informer que votre candidature au concours
                                <strong style="color: #10b981;">{{ $concours->libelle_concours }}</strong> a été
                                <strong style="color: #10b981;">VALIDÉE</strong> avec succès ! 🎉
                            </p>

                            <!-- Success Badge -->
                            <table width="100%" cellpadding="0" cellspacing="0" style="background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%); border-radius: 8px; margin: 25px 0;">
                                <tr>
                                    <td style="padding: 20px; text-align: center;">
                                        <p style="color: #065f46; margin: 0 0 8px 0; font-size: 14px; font-weight: bold;">✨ STATUT : VALIDÉE</p>
                                        <p style="color: #047857; margin: 0; font-size: 13px;">Votre dossier a été approuvé par l'administration</p>
                                    </td>
                                </tr>
                            </table>

                            <!-- Code Definitif Box -->
                            <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f8f9fa; border-left: 4px solid #10b981; margin: 25px 0;">
                                <tr>
                                    <td style="padding: 20px;">
                                        <p style="color: #666666; margin: 0 0 8px 0; font-size: 13px;">Votre code candidat définitif :</p>
                                        <p style="color: #10b981; font-size: 24px; font-weight: bold; margin: 0; letter-spacing: 1px;">
                                            {{ $candidature->code_candidat_def }}
                                        </p>
                                        <p style="color: #666666; margin: 8px 0 0 0; font-size: 12px; font-style: italic;">
                                            ⚠️ Conservez précieusement ce code, vous en aurez besoin le jour de l'examen
                                        </p>
                                    </td>
                                </tr>
                            </table>

                            <!-- Info Box -->
                            <table width="100%" cellpadding="0" cellspacing="0" style="border: 1px solid #e0e0e0; border-radius: 6px; margin: 25px 0;">
                                <tr>
                                    <td style="padding: 20px;">
                                        <h3 style="color: #333333; margin: 0 0 15px 0; font-size: 16px;">📋 Informations de l'examen</h3>
                                        <p style="color: #555555; font-size: 14px; line-height: 1.6; margin: 0 0 10px 0;">
                                            <strong>Date de l'examen :</strong> {{ $concours->date_examen->format('d/m/Y à H:i') }}
                                        </p>
                                        <p style="color: #555555; font-size: 14px; line-height: 1.6; margin: 0 0 10px 0;">
                                            <strong>Date de validation :</strong> {{ $candidature->date_validation->format('d/m/Y à H:i') }}
                                        </p>
                                        <p style="color: #555555; font-size: 14px; line-height: 1.6; margin: 0;">
                                            <strong>Statut :</strong> <span style="color: #10b981; font-weight: bold;">✅ APPROUVÉE</span>
                                        </p>
                                    </td>
                                </tr>
                            </table>

                            <!-- Download Button -->
                            <table width="100%" cellpadding="0" cellspacing="0" style="margin: 30px 0;">
                                <tr>
                                    <td align="center">
                                        <a href="{{ $downloadUrl }}" style="display: inline-block; background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: #ffffff; text-decoration: none; padding: 15px 40px; border-radius: 6px; font-size: 16px; font-weight: bold; box-shadow: 0 4px 6px rgba(16, 185, 129, 0.3);">
                                            📥 Télécharger ma fiche définitive
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <!-- Important Box -->
                            <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #fef3c7; border: 1px solid #f59e0b; border-radius: 6px; margin: 25px 0;">
                                <tr>
                                    <td style="padding: 20px;">
                                        <p style="color: #92400e; font-size: 14px; line-height: 1.6; margin: 0;">
                                            <strong>📌 Important le jour de l'examen :</strong><br>
                                            • Présentez-vous au centre d'examen <strong>30 minutes avant</strong> l'heure de début<br>
                                            • Apportez votre <strong>fiche de candidature imprimée</strong><br>
                                            • Munissez-vous de votre <strong>carte d'identité originale</strong><br>
                                            • Notez votre <strong>code candidat définitif</strong> : {{ $candidature->code_candidat_def }}<br>
                                            • Les retards ne seront pas tolérés
                                        </p>
                                    </td>
                                </tr>
                            </table>

                            <p style="color: #555555; font-size: 14px; line-height: 1.6; margin: 25px 0 0 0;">
                                Nous vous souhaitons bonne chance pour l'examen ! 🍀
                            </p>

                            <p style="color: #555555; font-size: 14px; line-height: 1.6; margin: 15px 0 0 0;">
                                Cordialement,<br>
                                <strong>L'équipe d'admission</strong>
                            </p>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f8f9fa; padding: 20px 30px; text-align: center; border-top: 1px solid #e0e0e0;">
                            <p style="color: #999999; font-size: 12px; line-height: 1.5; margin: 0;">
                                Cet email a été envoyé automatiquement, merci de ne pas y répondre.<br>
                                © {{ date('Y') }} - Tous droits réservés
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
