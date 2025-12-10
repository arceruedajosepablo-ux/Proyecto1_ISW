<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Reservation;
use App\Mail\ReservationNotification;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class NotifyPendingReservations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reservations:notify-pending {minutes=60 : Notificar reservas pendientes de hace más de N minutos}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Notifica a los conductores sobre reservas pendientes que llevan mucho tiempo sin atender';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $minutes = $this->argument('minutes');
        $cutoffTime = Carbon::now()->subMinutes($minutes);

        $this->info("Buscando reservas pendientes de hace más de {$minutes} minutos...");

        // Buscar reservas pendientes antiguas con sus relaciones
        $reservations = Reservation::with(['ride.user', 'passenger'])
            ->where('status', 'pending')
            ->where('created_at', '<=', $cutoffTime)
            ->get();

        if ($reservations->isEmpty()) {
            $this->info("No hay reservas pendientes de hace más de {$minutes} minutos.");
            return 0;
        }

        // Agrupar reservas por conductor
        $grouped = $reservations->groupBy('ride.user_id');

        $totalEmails = 0;
        foreach ($grouped as $driverId => $driverReservations) {
            $driver = $driverReservations->first()->ride->user;
            
            // Enviar un solo email con todas las reservas pendientes del conductor
            try {
                Mail::to($driver->email)->send(
                    new \App\Mail\PendingReservationsReminder($driver, $driverReservations)
                );
                
                $totalEmails++;
                $this->info("✓ Email enviado a {$driver->email} ({$driverReservations->count()} reservas)");
            } catch (\Exception $e) {
                $this->error("✗ Error enviando email a {$driver->email}: {$e->getMessage()}");
            }
        }

        $this->info("\nResumen: {$totalEmails} emails enviados para {$reservations->count()} reservas pendientes.");
        return 0;
    }
}
