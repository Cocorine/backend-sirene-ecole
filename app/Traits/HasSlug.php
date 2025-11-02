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

                // Vérifie si les colonnes roleable existent dans la table
                $hasRoleableColumns = in_array('roleable_id', $model->getConnection()
                        ->getSchemaBuilder()
                        ->getColumnListing($model->getTable()))
                    && in_array('roleable_type', $model->getConnection()
                        ->getSchemaBuilder()
                        ->getColumnListing($model->getTable()));

                while (static::where('slug', $uniqueSlug)
                            ->when($hasRoleableColumns && isset($model->roleable_id) && isset($model->roleable_type), function ($query) use ($model) {
                                return $query->where('roleable_id', $model->roleable_id)
                                             ->where('roleable_type', $model->roleable_type);
                            }, function ($query) use ($hasRoleableColumns) {
                                if ($hasRoleableColumns) {
                                    return $query->whereNull('roleable_id')->whereNull('roleable_type');
                                }
                                return $query; // ignore si pas de colonnes
                            })
                            ->exists()) {
                    $uniqueSlug = $baseSlug . '-' . $counter++;
                }

                $model->slug = $uniqueSlug;
            }
        });
    }
}
