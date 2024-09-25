<x-mail::message>
# Horário indisponível
Olá, {{ $pacienteEntity->nome }}!
Sua reserva não foi aprovada, pois o horário não está mais disponível.

<x-mail::panel>
    **Detalhes do Horário:**<br>
    - **UUID do Horário:** {{ $horarioReservadoEntity->horarioUuid }} <br>
    - **Médico:** {{ $medicoEntity->nome }}<br>
    - **Data:** {{ $horarioReservadoEntity->data->format('d/m/Y') }}<br>
    - **Hora de Início:** {{ $horarioReservadoEntity->horaInicio->format('H:i') }}<br>
    - **Hora de Fim:** {{ $horarioReservadoEntity->horaFim->format('H:i') }}<br>
    - **Status:** {{ $horarioReservadoEntity->getStatus()->name }}<br>
</x-mail::panel>

Obrigado,<br>
{{ config('app.name') }}
</x-mail::message>
