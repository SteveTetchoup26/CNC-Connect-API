<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Candidature Non Retenue</title>
</head>
<body style="margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f4f4f4;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f4f4f4; padding: 20px;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    <!-- Header -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); padding: 40px 30px; text-align: center;">
                            <h1 style="color: #ffffff; margin: 0; font-size: 28px;">Notification de candidature</h1>
                            <p style="color: #ffffff; margin: 10px 0 0 0; font-size: 16px;">Concernant votre inscription</p>
                        </td>
                    </tr>

                    <!-- Content -->
                    <tr>
                        <td style="padding: 40px 30px;">
                            <p style="color: #333333; font-size: 16px; line-height: 1.6; margin: 0 0 20px 0;">
                                Bonjour <strong>{{ $candidat->utilisateur->nom }} {{ $candidat->utilisateur->prenom }}</strong>,
                            </p>

                            <p style="color: #555555; font-size: 15px; line-height: 1.6; margin: 0 0 25px 0;">
                                Nous vous remercions de l'intérêt que vous portez à notre établissement et du temps que vous avez consacré à votre candidature pour le concours
                                <strong>{{ $concours->libelle_concours }}</strong>.
                            </p>

                            <p style="color: #555555; font-size: 15px; line-height: 1.6; margin: 0 0 25px 0;">
                                Après examen attentif de votre dossier, nous sommes au regret de vous informer que votre candidature n'a pas été retenue pour cette session.
                            </p>

                            <!-- Status Badge -->
                            <table width="100%" cellpadding="0" cellspacing="0" style="background: #fee2e2; border-radius: 8px; margin: 25px 0;">
                                <tr>
                                    <td style="padding: 20px; text-align: center;">
                                        <p style="color: #991b1b; margin: 0 0 8px 0; font-size: 14px; font-weight: bold;">❌ STATUT : NON RETENUE</p>
                                        <p style="color: #b91c1c; margin: 0; font-size: 13px;">Votre dossier a été examiné par l'administration</p>
                                    </td>
                                </tr>
                            </table>

                            <!-- Motif Box -->
                            <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #fef2f2; border-left: 4px solid #ef4444; margin: 25px 0;">
                                <tr>
                                    <td style="padding: 20px;">
                                        <h3 style="color: #991b1b; margin: 0 0 12px 0; font-size: 15px;">📋 Motif du rejet :</h3>
                                        <p style="color: #7f1d1d; font-size: 14px; line-height: 1.6; margin: 0;">
                                            {{ $motifRejet }}
                                        </p>
                                    </td>
                                </tr>
                            </table>

                            <!-- Info Box -->
                            <table width="100%" cellpadding="0" cellspacing="0" style="border: 1px solid #e0e0e0; border-radius: 6px; margin: 25px 0;">
                                <tr>
                                    <td style="padding: 20px;">
                                        <h3 style="color: #333333; margin: 0 0 15px 0; font-size: 16px;">📋 Informations de votre candidature</h3>
                                        <p style="color: #555555; font-size: 14px; line-height: 1.6; margin: 0 0 10px 0;">
                                            <strong>Code candidat :</strong> {{ $candidature->code_candidat_temp }}
                                        </p>
                                        <p style="color: #555555; font-size: 14px; line-height: 1.6; margin: 0 0 10px 0;">
                                            <strong>Concours :</strong> {{ $concours->libelle_concours }}
                                        </p>
                                        <p style="color: #555555; font-size: 14px; line-height: 1.6; margin: 0 0 10px 0;">
                                            <strong>Date de candidature :</strong> {{ $candidature->date_candidature->format('d/m/Y') }}
                                        </p>
                                        <p style="color: #555555; font-size: 14px; line-height: 1.6; margin: 0;">
                                            <strong>Statut :</strong> <span style="color: #ef4444; font-weight: bold;">❌ REJETÉE</span>
                                        </p>
                                    </td>
                                </tr>
                            </table>

                            <!-- Encouragement Box -->
                            <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #dbeafe; border: 1px solid #3b82f6; border-radius: 6px; margin: 25px 0;">
                                <tr>
                                    <td style="padding: 20px;">
                                        <p style="color: #1e3a8a; font-size: 14px; line-height: 1.6; margin: 0;">
                                            <strong>💡 Que faire maintenant ?</strong><br><br>
                                            • Vous pouvez corriger les éléments mentionnés dans le motif de rejet<br>
                                            • Préparer un nouveau dossier conforme aux exigences<br>
                                            • Vous inscrire à la prochaine session du concours<br>
                                            • Contacter le service d'admission pour plus d'informations
                                        </p>
                                    </td>
                                </tr>
                            </table>

                            <p style="color: #555555; font-size: 14px; line-height: 1.6; margin: 25px 0 0 0;">
                                Nous vous encourageons vivement à postuler de nouveau lors de nos prochaines sessions. Cette décision ne remet pas en cause vos compétences et nous restons convaincus de votre potentiel.
                            </p>

                            <p style="color: #555555; font-size: 14px; line-height: 1.6; margin: 15px 0 0 0;">
                                Nous vous souhaitons bonne continuation dans vos projets académiques.
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
                                Pour toute question, contactez le service d'admission.<br>
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
