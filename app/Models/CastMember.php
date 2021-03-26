<?php

namespace App;

namespace App\Models;

use App\Models\Traits\Uuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CastMember extends Model
{
    use SoftDeletes, Uuid;

    const MEMBER_DIRECTOR = 1;
    const MEMBER_ACTOR = 2;

    protected $fillable = ['name', 'type', 'is_active'];
    protected $dates = ['deleted_at'];
    protected $casts = ['id' => 'string', 'is_active' => 'boolean', 'type' => 'integer'];
    public $incrementing = false;

    public static function typesMembers()
    {
        return [self::MEMBER_DIRECTOR, self::MEMBER_ACTOR];
    }
}
