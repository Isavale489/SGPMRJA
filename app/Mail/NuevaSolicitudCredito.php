<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\SolicitudCredito;

class NuevaSolicitudCredito extends Mailable
{
    use Queueable, SerializesModels;

    public $solicitud;

    public function __construct(SolicitudCredito $solicitud)
    {
        $this->solicitud = $solicitud;
    }

    public function build()
    {
        return $this->subject('Nueva Solicitud de Crédito Recibida')
                    ->view('emails.nueva-solicitud');
    }
}
