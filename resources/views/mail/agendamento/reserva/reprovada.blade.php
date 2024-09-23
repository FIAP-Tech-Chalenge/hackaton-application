<x-mail::message>
# Reserva Reprovada
Ola, {{ $horarioReservadoEntity->pacienteEntity->nome }}!
Sua reserva foi reprovada.

<x-mail::panel>
    **Detalhes da Reserva:**<br>
    - **UUID do Horário:** {{ $horarioReservadoEntity->horarioUuid }} <br>
    - **Médico:** {{ $horarioReservadoEntity->medicoEntity->nome }}<br>
    - **Paciente:** {{ $horarioReservadoEntity->pacienteEntity->nome }}<br>
    - **Data:** {{ $horarioReservadoEntity->data->format('d/m/Y') }}<br>
    - **Hora de Início:** {{ $horarioReservadoEntity->horaInicio->format('H:i') }}<br>
    - **Hora de Fim:** {{ $horarioReservadoEntity->horaFim->format('H:i') }}<br>
    - **Status:** {{ $horarioReservadoEntity->getStatus()->name }}<br>
</x-mail::panel>


Obrigado,<br>
{{ config('app.name') }}
</x-mail::message>
