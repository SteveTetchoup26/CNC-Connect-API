<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription réussie</title>
</head>
<body style="margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f4f4f4;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f4f4f4; padding: 20px;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    <!-- Header -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 40px 30px; text-align: center;">
                            <h1 style="color: #ffffff; margin: 0; font-size: 28px;">🎉 Félicitations !</h1>
                            <p style="color: #ffffff; margin: 10px 0 0 0; font-size: 16px;">Votre inscription a été enregistrée avec succès</p>
                        </td>
                    </tr>

                    <!-- Content -->
                    <tr>
                        <td style="padding: 40px 30px;">
                            <p style="color: #333333; font-size: 16px; line-height: 1.6; margin: 0 0 20px 0;">
                                Bonjour <strong>{{ $candidat->utilisateur->nom }} {{ $candidat->utilisateur->prenom }}</strong>,
                            </p>

                            <p style="color: #555555; font-size: 15px; line-height: 1.6; margin: 0 0 25px 0;">
                                Nous avons le plaisir de vous confirmer que votre inscription au concours
                                <strong style="color: #667eea;">{{ $concours->libelle_concours }}</strong> a été enregistrée avec succès.
                            </p>

                            <!-- Code Box -->
                            <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f8f9fa; border-left: 4px solid #667eea; margin: 25px 0;">
                                <tr>
                                    <td style="padding: 20px;">
                                        <p style="color: #666666; margin: 0 0 8px 0; font-size: 13px;">Votre code temporaire :</p>
                                        <p style="color: #d9534f; font-size: 22px; font-weight: bold; margin: 0; letter-spacing: 1px;">
                                            {{ $candidature->code_candidat_temp }}
                                        </p>
                                    </td>
                                </tr>
                            </table>

                            <!-- Info Box -->
                            <table width="100%" cellpadding="0" cellspacing="0" style="border: 1px solid #e0e0e0; border-radius: 6px; margin: 25px 0;">
                                <tr>
                                    <td style="padding: 20px;">
                                        <h3 style="color: #333333; margin: 0 0 15px 0; font-size: 16px;">📋 Informations importantes</h3>
                                        <p style="color: #555555; font-size: 14px; line-height: 1.6; margin: 0 0 10px 0;">
                                            <strong>Date de l'examen :</strong> {{ $concours->date_examen->format('d/m/Y à H:i') }}
                                        </p>
                                        <p style="color: #555555; font-size: 14px; line-height: 1.6; margin: 0 0 10px 0;">
                                            <strong>Date limite de dépôt :</strong> {{ $concours->date_limite->format('d/m/Y') }}
                                        </p>
                                        <p style="color: #555555; font-size: 14px; line-height: 1.6; margin: 0;">
                                            <strong>Statut actuel :</strong> <span style="color: #ffc107;">⏳ En attente de validation</span>
                                        </p>
                                    </td>
                                </tr>
                            </table>

                            <!-- Download Button -->
                            <table width="100%" cellpadding="0" cellspacing="0" style="margin: 30px 0;">
                                <tr>
                                    <td align="center">
                                        <a href="{{ $downloadUrl }}" style="display: inline-block; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #ffffff; text-decoration: none; padding: 15px 40px; border-radius: 6px; font-size: 16px; font-weight: bold; box-shadow: 0 4px 6px rgba(102, 126, 234, 0.3);">
                                            📥 Télécharger ma fiche de candidature
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <!-- Warning Box -->
                            <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #fff3cd; border: 1px solid #ffc107; border-radius: 6px; margin: 25px 0;">
                                <tr>
                                    <td style="padding: 20px;">
                                        <p style="color: #856404; font-size: 14px; line-height: 1.6; margin: 0;">
                                            <strong>⚠️ Prochaines étapes :</strong><br>
                                            1. Téléchargez et imprimez votre fiche de candidature<br>
                                            2. Préparez tous les documents requis<br>
                                            3. Déposez votre dossier complet au centre de dépôt avant le <strong>{{ $concours->date_limite->format('d/m/Y') }}</strong><br>
                                            4. Après validation, vous recevrez votre code candidat définitif
                                        </p>
                                    </td>
                                </tr>
                            </table>

                            <p style="color: #555555; font-size: 14px; line-height: 1.6; margin: 25px 0 0 0;">
                                Si vous avez des questions, n'hésitez pas à nous contacter.
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
