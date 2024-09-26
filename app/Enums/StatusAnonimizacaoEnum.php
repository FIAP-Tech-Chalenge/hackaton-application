<?php

namespace App\Enums;

enum StatusAnonimizacaoEnum: int
{
    case AGENDADO = 0;
    case ANONIMIZADO = 1;
    case CANCELADO = 2;
}
