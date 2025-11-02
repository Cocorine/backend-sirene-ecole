<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

trait HasNumeroSerie
{
    /**
     * Boot the trait and automatically generate numero_serie on model creation
     */
    protected static function bootHasNumeroSerie(): void
    {
        static::creating(function (Model $model) {
            if (empty($model->numero_serie)) {
                $model->numero_serie = self::generateNumeroSerie();
            }
        });
    }

    /**
     * Generate a unique numero serie
     */
    public static function generateNumeroSerie(): string
    {
        do {
            $numeroSerie = 'SIR-' . strtoupper(Str::random(8)) . '-' . date('Y');
        } while (static::where('numero_serie', $numeroSerie)->exists());

        return $numeroSerie;
    }
}
