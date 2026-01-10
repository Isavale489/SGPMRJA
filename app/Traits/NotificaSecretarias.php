<?php

namespace App\Traits;

use App\Models\User;
use App\Jobs\EnviarNotificacionSolicitud; 
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\NuevaSolicitudCredito;

trait NotificaSecretarias
{
    protected function notificarSecretarias($solicitud)
    {
        try { 
            // Obtener los correos de las secretarias
            $emailsSecretarias = User::where('role', 'Secretaria')
                                    ->pluck('email')
                                    ->toArray();

            Log::info('Correos de secretarias encontrados: ' . implode(', ', $emailsSecretarias));

            if (empty($emailsSecretarias)) { 
                Log::warning('No se encontraron secretarias, usando correo por defecto');
                $emailsSecretarias = [
                    'secretaria@financiera.com',
                ];
            }  
            
            // Usar una técnica especial para ejecutar código después de la respuesta
            $this->sendEmailsAfterResponse($solicitud, $emailsSecretarias);
            
            return true;
        } catch (\Exception $e) { 
            Log::error('Error al notificar secretarias: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            return false;
        }
    }
    
    /**
     * Envía los correos después de la respuesta HTTP
     */
    protected function sendEmailsAfterResponse($solicitud, $emailsSecretarias)
    {
        // Registrar una función de cierre para ejecutarse después de la respuesta
        register_shutdown_function(function () use ($solicitud, $emailsSecretarias) {
            try {
                // Ignorar la desconexión del cliente
                ignore_user_abort(true);
                
                // Establecer tiempo límite para evitar que el script termine prematuramente
                set_time_limit(0);
                
                // Cerrar la sesión para liberar recursos
                if (session_status() === PHP_SESSION_ACTIVE) {
                    session_write_close();
                }
                
                // Registrar inicio del proceso en segundo plano
                Log::info('Iniciando envío de notificaciones en segundo plano para solicitud #' . $solicitud->id);
                
                // Enviar correos a cada secretaria
                foreach ($emailsSecretarias as $email) {
                    try {
                        Mail::to($email)->send(new NuevaSolicitudCredito($solicitud));
                        Log::info("Email enviado exitosamente a: $email");
                    } catch (\Exception $e) {
                        Log::error("Error al enviar email a $email: " . $e->getMessage());
                    }
                }
                
                Log::info('Finalizado envío de notificaciones en segundo plano para solicitud #' . $solicitud->id);
            } catch (\Exception $e) {
                Log::error('Error en proceso en segundo plano: ' . $e->getMessage());
                Log::error($e->getTraceAsString());
            }
        });
        
        Log::info('Notificación programada para ejecutarse después de la respuesta');
    }
}
