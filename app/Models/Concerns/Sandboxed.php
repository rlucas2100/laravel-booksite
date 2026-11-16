<?php

namespace App\Models\Concerns;

use App\Support\Sandbox;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * For models with a nullable "sandbox" column. See App\Support\Sandbox.
 *
 * - New records are tagged with the visitor's token (in demo mode only).
 * - Queries only return public records plus the visitor's own.
 */
trait Sandboxed
{
    public static function bootSandboxed(): void
    {
        static::creating(function (Model $model) {
            if ($model->getAttribute('sandbox') === null) {
                $model->setAttribute('sandbox', Sandbox::tagForNewRecord());
            }
        });

        if (static::hidesSandboxedByDefault()) {
            static::addGlobalScope('sandbox', function (Builder $query) {
                static::applySandbox($query);
            });
        }
    }

    /** Models that must always be findable (users signing in) return false and use visibleHere() by hand. */
    protected static function hidesSandboxedByDefault(): bool
    {
        return true;
    }

    public function scopeVisibleHere(Builder $query): void
    {
        static::applySandbox($query);
    }

    protected static function applySandbox(Builder $query): void
    {
        if (Sandbox::unrestricted()) {
            return;
        }

        $column = $query->qualifyColumn('sandbox');
        $token = Sandbox::token();

        $query->where(function (Builder $query) use ($column, $token) {
            $query->whereNull($column);

            if ($token) {
                $query->orWhere($column, $token);
            }
        });
    }

    public function isSandboxed(): bool
    {
        return $this->getAttribute('sandbox') !== null;
    }
}
