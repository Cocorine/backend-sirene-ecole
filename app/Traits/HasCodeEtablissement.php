<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

trait HasCodeEtablissement
{
    /**
     * Boot the trait and automatically generate code_etablissement on model creation
     */
    protected static function bootHasCodeEtablissement(): void
    {
        static::creating(function (Model $model) {
            if (empty($model->code_etablissement)) {
                $model->code_etablissement = self::generateUniqueCode();
            }
        });
    }

    /**
     * Générer un code établissement unique
     */
    public static function generateUniqueCode(): string
    {
        do {
            $code = 'ECO' . strtoupper(Str::random(6));
        } while (static::where('code_etablissement', $code)->exists());

        return $code;
    }
}
