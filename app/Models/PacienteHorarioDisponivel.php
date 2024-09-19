<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PacienteHorarioDisponivel extends Model
{
    use HasFactory;
    use HasUuids;

    public $incrementing = false;
    protected $primaryKey = 'horario_disponivel_uuid';
    protected $keyType = 'string';
    protected $table = 'paciente_horarios_disponiveis';

    protected $fillable = [
        'horario_disponivel_uuid',
        'paciente_uuid',
        'assinatura_confirmacao',
        'confirmado_em'
    ];

    public function horarioDisponivel(): BelongsTo
    {
        return $this->belongsTo(HorarioDisponivel::class, 'horario_disponivel_uuid', 'uuid');
    }

    public function paciente(): BelongsTo
    {
        return $this->belongsTo(Paciente::class, 'paciente_uuid', 'uuid');
    }
}
