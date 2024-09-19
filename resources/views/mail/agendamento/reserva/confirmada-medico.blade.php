<x-mail::message>
# Novo Agendamento de Consulta Confirmado

Olá, Dr(a) {{ $user->medico->name }}!

Obrigado,<br>
{{ config('app.name') }}
</x-mail::message>
