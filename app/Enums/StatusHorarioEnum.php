<?php

namespace App\Enums;

enum StatusHorarioEnum: int
{
    case INDISPONIVEL = 0;
    case DISPONIVEL = 1;
    case RESERVADO = 2;
    case CONFIRMADO = 3;
    case CANCELADO = 4;
}
