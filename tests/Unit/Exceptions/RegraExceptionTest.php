<?php

namespace Tests\Unit\Exceptions;

use App\Modules\Shared\Exceptions\RegraException;
use PHPUnit\Framework\TestCase;

class RegraExceptionTest extends TestCase
{
    public function test_example()
    {
        $exception = new RegraException(
            message: 'Erro de regra de negócio',
            code: 1,
            errors: []
        );
        $this->assertIsArray($exception->getErrors());
    }
}
