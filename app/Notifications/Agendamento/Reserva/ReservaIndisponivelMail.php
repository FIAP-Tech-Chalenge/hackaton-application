<?php

namespace App\Notifications\Agendamento\Reserva;

use App\Modules\Shared\Entities\HorarioEntity;
use App\Modules\Shared\Entities\MedicoEntity;
use App\Modules\Shared\Entities\PacienteEntity;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReservaIndisponivelMail extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly HorarioEntity $horarioEntity,
        private readonly MedicoEntity $medicoEntity,
        private readonly PacienteEntity $pacienteEntity
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Reserva reprovada')
            ->markdown(
                'mail.agendamento.reserva.horario-indisponivel',
                [
                    'horarioEntity' => $this->horarioEntity,
                    'medicoEntity' => $this->medicoEntity,
                    'pacienteEntity' => $this->pacienteEntity,
                ]
            );
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
