<?php

namespace App\Helpers;

use Illuminate\Database\Eloquent\Builder;

final class BuilderHelper
{
    public static function overlap(
        Builder $baseBuilder,
        string $primeiraColuna,
        string $segundaColuna,
        mixed $primeiroValor,
        mixed $segundoValor
    ): Builder {
        return $baseBuilder
            ->where(fn(Builder $builder) => $builder
                ->whereBetween($primeiraColuna, [$primeiroValor, $segundoValor])
                ->orWhereBetween($segundaColuna, [$primeiroValor, $segundoValor])
                ->orWhere(
                    fn(Builder $builder) => $builder
                        ->where($primeiraColuna, '<=', $primeiroValor)
                        ->where($segundaColuna, '>=', $segundoValor)
                )
            );
    }
}
