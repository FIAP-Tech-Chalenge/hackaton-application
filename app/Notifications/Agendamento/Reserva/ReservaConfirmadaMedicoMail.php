<?php

namespace App\Notifications\Agendamento\Reserva;

use App\Modules\Shared\Entities\HorarioReservadoEntity;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReservaConfirmadaMedicoMail extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(private readonly HorarioReservadoEntity $horarioReservadoEntity)
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Novo agendamento')
            ->markdown(
                'mail.agendamento.reserva.confirmada-medico',
                ['horarioReservadoEntity' => $this->horarioReservadoEntity]
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
