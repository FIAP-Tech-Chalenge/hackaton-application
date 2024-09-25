<x-mail::message>
# Reserva Reprovada
Olá, {{ $horarioEntity->pacienteEntity->nome }}!
Sua reserva foi reprovada.

<x-mail::panel>
    **Detalhes da Reserva:**<br>
    - **UUID do Horário:** {{ $horarioEntity->horarioUuid }} <br>
    - **Médico:** {{ $horarioEntity->medicoEntity->nome }}<br>
    - **Paciente:** {{ $horarioEntity->pacienteEntity->nome }}<br>
    - **Data:** {{ $horarioEntity->data->format('d/m/Y') }}<br>
    - **Hora de Início:** {{ $horarioEntity->horaInicio->format('H:i') }}<br>
    - **Hora de Fim:** {{ $horarioEntity->horaFim->format('H:i') }}<br>
    - **Status:** {{ $horarioEntity->getStatus()->name }}<br>
</x-mail::panel>

Obrigado,<br>
{{ config('app.name') }}
</x-mail::message>
