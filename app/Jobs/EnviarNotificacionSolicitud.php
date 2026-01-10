<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\SolicitudCredito; 
use Illuminate\Support\Facades\Mail;
use App\Mail\NuevaSolicitudCredito;
use App\Models\User;

class EnviarNotificacionSolicitud implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $solicitudId;

    /**
     * Create a new job instance.
     *
     * @param int $solicitudId
     * @return void
     */
    public function __construct($solicitudId)
    {
        $this->solicitudId = $solicitudId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try { 
            $solicitud = SolicitudCredito::find($this->solicitudId); 
            if (!$solicitud) { 
                return;
            }  
            $emailsSecretarias = User::where('role', 'Secretaria')
                                    ->pluck('email')
                                    ->toArray();
              
            if (empty($emailsSecretarias)) {  
                return;
            }  
            foreach ($emailsSecretarias as $email) {
                try {
                    Mail::to($email)->send(new NuevaSolicitudCredito($solicitud));
                } catch (\Exception $e) { 
                }
            }
        } catch (\Exception $e) { 
        }
    }
}
