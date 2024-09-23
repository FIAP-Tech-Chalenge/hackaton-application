<x-mail::message>
# Agendamento de consulta

Ola, {{ $horarioReservadoEntity->pacienteEntity->nome }}!
Sua reserva foi confirmada com sucesso!

<x-mail::panel>
    **Detalhes da Reserva:**
    - **UUID do Horário:** {{ $horarioReservadoEntity->horarioUuid }}<br>
    - **Médico:** {{ $horarioReservadoEntity->medicoEntity->nome }}<br>
    - **Paciente:** {{ $horarioReservadoEntity->pacienteEntity->nome }}<br>
    - **Data:** {{ $horarioReservadoEntity->data->toFormattedDateString() }}<br>
    - **Hora de Início:** {{ $horarioReservadoEntity->horaInicio->format('H:i') }}<br>
    - **Hora de Fim:** {{ $horarioReservadoEntity->horaFim->format('H:i') }}<br>
    - **Status:** {{ $horarioReservadoEntity->getStatus()->name}}<br>
</x-mail::panel>

Obrigado,<br>
{{ config('app.name') }}
</x-mail::message>
