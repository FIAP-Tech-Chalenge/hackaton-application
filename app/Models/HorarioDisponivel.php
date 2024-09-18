<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HorarioDisponivel extends Model
{
    use HasFactory;
    use HasUuids;

    public $incrementing = false;
    protected $primaryKey = 'uuid';
    protected $keyType = 'string';
    protected $table = 'horarios_disponiveis';

    protected $fillable = [
        'uuid',
        'medico_uuid',
        'data',
        'hora_inicio',
        'hora_fim',
        'status',
    ];
}
