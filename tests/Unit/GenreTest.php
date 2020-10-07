<?php

namespace Tests\Unit;

use App\Models\Genre;
use App\Models\Traits\Uuid;
use Illuminate\Database\Eloquent\SoftDeletes;
use PHPUnit\Framework\TestCase;

class GenreTest extends TestCase
{

    public function testFillable()
    {
        $arrExpected = ['name', 'is_active'];

        $category = new Genre();
        $this->assertEquals($arrExpected, $category->getFillable());
    }

    public function testIfUseTraits() {
        $traits = [
            SoftDeletes::class,
            Uuid::class
        ];
        $genreTraits = array_keys(class_uses(Genre::class));
        $this->assertEquals($traits, $genreTraits);
    }

    public function testCasts() {
        $casts = ['id' => 'string'];
        $genre = new Genre();
        $this->assertEquals($casts, $genre->getCasts());
    }

    public function testIncrementing() {
        $genre = new Genre();
        $this->assertFalse($genre->incrementing);
    }

    public function testDatesAttributes() {
        $dates = ['deleted_at', 'created_at', 'updated_at'];
        $genre = new Genre();
        foreach ($dates as $date) {
            $this->assertContains($date, $genre->getDates());
        }
        $this->assertCount(sizeof($dates), $genre->getDates());
    }
}
