<?php

namespace Tests\Unit\Models;

use App\Models\CastMember;
use App\Models\Traits\Uuid;
use Illuminate\Database\Eloquent\SoftDeletes;
use PHPUnit\Framework\TestCase;

class CastMemberUnitTest extends TestCase
{

    public function testFillable()
    {
        $arrExpected = ['name', 'type', 'is_active'];

        $category = new CastMember();
        $this->assertEquals($arrExpected, $category->getFillable());
    }

    public function testIfUseTraits() {
        $traits = [
            SoftDeletes::class,
            Uuid::class
        ];
        $castMember = array_keys(class_uses(CastMember::class));
        $this->assertEquals($traits, $castMember);
    }

    public function testCasts() {
        $casts = ['id' => 'string', 'is_active' => 'boolean', 'type' => 'integer'];
        $category = new CastMember();
        $this->assertEquals($casts, $category->getCasts());
    }

    public function testIncrementing() {
        $category = new CastMember();
        $this->assertFalse($category->incrementing);
    }

    public function testDatesAttributes() {
        $dates = ['deleted_at', 'created_at', 'updated_at'];
        $category = new CastMember();
        foreach ($dates as $date) {
            $this->assertContains($date, $category->getDates());
        }
        $this->assertCount(sizeof($dates), $category->getDates());
    }
}
