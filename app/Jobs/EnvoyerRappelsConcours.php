<?php

namespace App\Jobs;

use App\Models\CandidatureRappel;
use App\Notifications\RappelConcoursNotification;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;


class EnvoyerRappelsConcours
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        $maintenant = Carbon::now();
        $dateDuJour = $maintenant->toDateString();
        $heureCourante = $maintenant->format('H:i');

        Log::info("NOW: " . $maintenant);
        Log::info("DATE: " . $dateDuJour);
        Log::info("HEURE: " . $heureCourante);


        // Récupérer les rappels à envoyer
        $rappels = CandidatureRappel::with('candidature.candidat.utilisateur', 'candidature.concours')
            ->where('email_envoye', false)
            ->whereDate('date_rappel', $dateDuJour)
            ->whereTime('heure_rappel', '<=', $heureCourante)
            ->get();

        Log::info("Nombre de rappels trouvés : " . $rappels->count());

        foreach ($rappels as $rappel) {
            try {
                // Envoyer la notification

                $user = $rappel->candidature->candidat->utilisateur;

                Log::info("=== TRAITEMENT RAPPEL ID: {$rappel->id} ===");
                Log::info("Candidature ID: {$rappel->candidature_id}");
                Log::info("User ID: {$user->id}");
                Log::info("User Name: {$user->name}");
                Log::info("User Email: {$user->email}"); // ← L'EMAIL ICI
                Log::info("Date rappel: {$rappel->date_rappel}");
                Log::info("Heure rappel: {$rappel->heure_rappel}");

                if (empty($user->email)) {
                    Log::error("❌ EMAIL VIDE pour User ID: {$user->id}");
                    continue;
                }

                $rappel->candidature->candidat->utilisateur->notify(
                    new RappelConcoursNotification($rappel->candidature)
                );

                // Marquer comme envoyé
                $rappel->update([
                    'email_envoye' => true,
                    'date_envoi_email' => now(),
                ]);

                Log::info("Rappel envoyé pour candidature ID: {$rappel->candidature_id}");
            } catch (\Exception $e) {
                Log::error('Erreur envoi rappel: ' . $e->getMessage());
            }
        }
    }
}
