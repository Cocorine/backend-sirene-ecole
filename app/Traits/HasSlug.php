<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\Models\Ecole; // Assuming Ecole model exists

trait HasSlug
{
    /**
     * Boot the trait and automatically generate slug on model creation.
     */
    protected static function bootHasSlug(): void
    {
        static::creating(function (Model $model) {
            if (empty($model->slug) && !empty($model->nom)) {
                $baseSlug = Str::slug($model->nom);
                $uniqueSlug = $baseSlug;
                $counter = 1;

                // Append roleable identifier to ensure uniqueness within the scope
                $scopeIdentifier = '';
                if (isset($model->roleable_id) && isset($model->roleable_type)) {
                    // If roleable is an Ecole, append its code
                    if ($model->roleable_type === Ecole::class) {
                        $ecole = Ecole::find($model->roleable_id);
                        if ($ecole && $ecole->code_etablissement) {
                            $scopeIdentifier = '-' . Str::slug($ecole->code_etablissement);
                        }
                    } else {
                        $scopeIdentifier = '-' . $model->roleable_id;
                    }
                }

                while (static::where('slug', $uniqueSlug . $scopeIdentifier)
                            ->when(isset($model->roleable_id) && isset($model->roleable_type), function ($query) use ($model) {
                                return $query->where('roleable_id', $model->roleable_id)
                                             ->where('roleable_type', $model->roleable_type);
                            }, function ($query) {
                                return $query->whereNull('roleable_id')->whereNull('roleable_type');
                            })
                            ->exists()) {
                    $uniqueSlug = $baseSlug . '-' . $counter++;
                }
                $model->slug = $uniqueSlug . $scopeIdentifier;
            }
        });
    }
}