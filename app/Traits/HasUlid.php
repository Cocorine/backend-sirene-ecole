<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Symfony\Component\Uid\Ulid;

trait HasUlid
{
    /**
     * Boot the trait and automatically generate ULID on model creation
     */
    protected static function bootHasUlid(): void
    {
        static::creating(function (Model $model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) Ulid::generate();
            }
        });
        static::saving(function (Model $model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) Ulid::generate();
            }
        });
    }

    /**
     * Get the value indicating whether the IDs are incrementing.
     */
    public function getIncrementing(): bool
    {
        return false;
    }

    /**
     * Get the auto-incrementing key type.
     */
    public function getKeyType(): string
    {
        return 'string';
    }

    /**
     * Generate a new ULID
     */
    public static function generateUlid(): string
    {
        return (string) Ulid::generate();
    }

    /**
     * Check if a given string is a valid ULID
     */
    public static function isValidUlid(string $ulid): bool
    {
        return Ulid::isValid($ulid);
    }

    /**
     * Convert ULID to DateTime
     */
    public function getUlidTimestamp(): ?\DateTimeImmutable
    {
        if ($this->{$this->getKeyName()}) {
            return Ulid::fromString($this->{$this->getKeyName()})->getDateTime();
        }

        return null;
    }
}
