<?php

namespace App\Notifications\Agendamento\Reserva;

use App\Modules\Shared\Entities\HorarioReservadoEntity;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReservaConfirmadaPacienteMail extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly HorarioReservadoEntity $horarioReservadoEntity)
    {
        //
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Reserva confirmada')
            ->markdown('mail.agendamento.reserva.confirmada-paciente', [
                'horarioReservadoEntity' => $this->horarioReservadoEntity,
            ]);
    }
}
