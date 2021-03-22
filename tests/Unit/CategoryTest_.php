<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\Traits\Uuid;
use Illuminate\Database\Eloquent\SoftDeletes;
use PHPUnit\Framework\TestCase;

class CategoryTest_ extends TestCase
{

    public function testFillable()
    {
        $arrExpected = ['name', 'description', 'is_active'];

        $category = new Category();
        $this->assertEquals($arrExpected, $category->getFillable());
    }

    public function testIfUseTraits() {
        $traits = [
            SoftDeletes::class,
            Uuid::class
        ];
        $categoryTraits = array_keys(class_uses(Category::class));
        $this->assertEquals($traits, $categoryTraits);
    }

    public function testCasts() {
        $casts = ['id' => 'string', 'is_active' => 'boolean'];
        $category = new Category();
        $this->assertEquals($casts, $category->getCasts());
    }

    public function testIncrementing() {
        $category = new Category();
        $this->assertFalse($category->incrementing);
    }

    public function testDatesAttributes() {
        $dates = ['deleted_at', 'created_at', 'updated_at'];
        $category = new Category();
        foreach ($dates as $date) {
            $this->assertContains($date, $category->getDates());
        }
        $this->assertCount(sizeof($dates), $category->getDates());
    }
}
