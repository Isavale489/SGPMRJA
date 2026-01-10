<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\SolicitudCredito;

class NuevaSolicitudCredito extends Notification implements ShouldQueue
{
    use Queueable;

    protected $solicitud;

    public function __construct(SolicitudCredito $solicitud)
    {
        $this->solicitud = $solicitud;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Nueva Solicitud de Crédito Recibida')
            ->view('emails.nueva-solicitud', [
                'solicitud' => $this->solicitud,
                'notifiable' => $notifiable
            ]);
    }
}
