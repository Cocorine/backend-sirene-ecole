<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

trait SoftDeletesUniqueFields
{
    /**
     * Boot the trait.
     *
     * @return void
     */
    protected static function bootSoftDeletesUniqueFields()
    {
        static::deleting(function (Model $model) {
            // Only apply to soft deletes
            if (in_array('Illuminate\Database\Eloquent\SoftDeletes', class_uses($model)) && !$model->isForceDeleting()) {
                $uniqueFields = $model->getUniqueSoftDeleteFields();

                foreach ($uniqueFields as $field) {
                    if ($model->isDirty($field) === false) { // Only modify if not already changed
                        $suffix = '_del_' . Str::random(6);
                        $maxLength = 20; // Assuming a max length of 20 for problematic fields like 'telephone'
                        $originalValue = $model->{$field};
                        $availableLength = $maxLength - strlen($suffix);

                        if (strlen($originalValue) > $availableLength) {
                            $originalValue = substr($originalValue, 0, $availableLength);
                        }

                        $model->{$field} = $originalValue . $suffix;
                    }
                }
                $model->saveQuietly(); // Save without re-triggering events
            }
        });
    }

    /**
     * Get the unique fields that should be updated on soft delete.
     *
     * @return array
     */
    protected function getUniqueSoftDeleteFields(): array
    {
        // This method should be overridden by the model using the trait
        // Example: return ['email', 'username'];
        return [];
    }
}
