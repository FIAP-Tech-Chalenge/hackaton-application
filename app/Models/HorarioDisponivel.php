<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property mixed $uuid
 * @property mixed $hora_inicio
 * @property mixed $hora_fim
 * @property mixed $status
 * @property mixed $medico_uuid
 * @property mixed $data
 */
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

    public function medico(): BelongsTo
    {
        return $this->belongsTo(Medico::class, 'medico_uuid', 'uuid');
    }
}
