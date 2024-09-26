<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SolicitacaoAnonimizacao extends Model
{
    use HasFactory;
    use HasUuids;

    public $incrementing = false;
    protected $table = 'solicitacao_anonimizacoes';
    protected $primaryKey = 'uuid';
    protected $keyType = 'string';

    protected $fillable = [
        'uuid',
        'user_id',
        'data_solicitacao',
        'data_anonimizacao',
        'status',
        'job_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
