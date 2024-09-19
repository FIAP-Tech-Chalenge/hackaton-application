
<x-mail::message>
# Reserva Reprovada
Ola, {{ $user->name }}!
Sua reserva foi reprovada.

<x-mail::panel>
    **Detalhes da Reserva:**
    - **UUID do Horário:** {{ $horarioReservadoEntity-> }}
    - **Médico:** {{ $horarioReservadoEntity->medicoEntity->name }}
    - **Paciente:** {{ $horarioReservadoEntity->pacienteEntity->name }}
    - **Data:** {{ $horarioReservadoEntity->data->toFormattedDateString() }}
    - **Hora de Início:** {{ $horarioReservadoEntity->horaInicio->format('H:i') }}
    - **Hora de Fim:** {{ $horarioReservadoEntity->horaFim->format('H:i') }}
    - **Status:** {{ $horarioReservadoEntity->getStatus()->label() }}
</x-mail::panel>


Obrigado,<br>
{{ config('app.name') }}
</x-mail::message>