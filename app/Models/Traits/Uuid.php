<?php

namespace App\Models\Traits;

use Ramsey\Uuid\Uuid as RamseyUuid;

trait Uuid
{
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($obj) {
            $obj->id = RamseyUuid::uuid4();
        });
    }
}
