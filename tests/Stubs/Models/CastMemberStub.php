<?php

namespace Tests\Stubs\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;

class CastMemberStub extends Model
{
    const MEMBER_DIRECTOR = 1;
    const MEMBER_ACTOR = 2;

    protected $table = 'cast_members_stubs';
    protected $fillable = ['name', 'type'];

    public static function typesMembers()
    {
        return [self::MEMBER_DIRECTOR, self::MEMBER_ACTOR];
    }

    public static function createTable()
    {
        \Schema::create('cast_members_stubs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->smallInteger('type');
            $table->timestamps();
        });
    }

    public static function dropTable()
    {
        \Schema::dropIfExists('cast_members_stubs');
    }
}
