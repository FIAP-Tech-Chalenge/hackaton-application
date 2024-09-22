<?php

namespace Tests\Unit\Entities\Medicos;

use App\Modules\Medicos\Entities\Horarios\PeriodoAtendimento;
use App\Modules\Shared\Exceptions\Horarios\HoraInicialMaiorQueFinalException;
use Carbon\Carbon;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('entities')]
#[Group('entities_intervalo')]
class PeriodoAtendimentoTest extends TestCase
{
    public function test_deve_lanca_excecao_quando_intervalo_for_invalido(): void
    {
        $this->expectException(HoraInicialMaiorQueFinalException::class);
        $this->expectExceptionMessage('Horário inválido');

        new PeriodoAtendimento(
            Carbon::parse('08:00'),
            Carbon::parse('07:00')
        );
    }
}
