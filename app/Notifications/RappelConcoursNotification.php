<?php

namespace App\Notifications;

use App\Models\Candidature;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RappelConcoursNotification extends Notification
{
    use Queueable;

    protected $candidature;

    public function __construct(Candidature $candidature)
    {
        $this->candidature = $candidature;
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $concours = $this->candidature->concours;
        $centreExamen = $this->candidature->centreExamen;

        return (new MailMessage)
            ->subject('Rappel - Concours ' . $concours->libelle_concours)
            ->greeting('Bonjour ' . $notifiable->name . ',')
            ->line('Ceci est un rappel concernant votre concours :')
            ->line('**Concours :** ' . $concours->libelle_concours)
            ->line('**Date de l\'examen :** ' . $concours->date_examen->format('d/m/Y'))
            ->line('**Heure :** ' . $concours->heure_examen)
            ->line('**Centre d\'examen :** ' . $centreExamen->libelle_centre . ' - ' . $centreExamen->ville_centre)
            ->line('N\'oubliez pas de vous présenter avec votre pièce d\'identité et votre convocation.')
            ->action('Télécharger ma fiche', url('/api/candidature/' . $this->candidature->id . '/fiche/download'))
            ->line('Bonne chance pour votre concours !');
    }
}
