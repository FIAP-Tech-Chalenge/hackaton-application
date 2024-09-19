<x-mail::message>
# Agendamento de consulta

Ola, {{ $user->name }}!
Sua reserva foi confirmada com sucesso!

<x-mail::panel>
    **Detalhes da Reserva:**
    - **UUID do Horário:** {{ $horarioReservadoEntity-> }}
    - **Médico:** {{ $horarioReservadoEntity->medicoEntity->name }}
    - **Paciente:** {{ $horarioReservadoEntity->pacienteEntity->name }}
    - **Data:** {{ $horarioReservadoEntity->data->toFormattedDateString() }}
    - **Hora de Início:** {{ $horarioReservadoEntity->horaInicio->format('H:i') }}
    - **Hora de Fim:** {{ $horarioReservadoEntity->horaFim->format('H:i') }}
    - **Status:** {{ $horarioReservadoEntity->getStatus()->name}}
</x-mail::panel>

Obrigado,<br>
{{ config('app.name') }}
</x-mail::message>
