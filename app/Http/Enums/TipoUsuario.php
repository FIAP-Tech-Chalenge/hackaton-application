<?php

namespace App\Http\Enums;

enum TipoUsuario: string
{
    case MEDICO = 'medico';
    case PACIENTE = 'paciente';
}
