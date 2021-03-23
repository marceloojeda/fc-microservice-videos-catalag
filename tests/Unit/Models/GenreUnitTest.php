<?php

namespace Tests\Unit;

use App\Models\Genre;
use App\Models\Traits\Uuid;
use Illuminate\Database\Eloquent\SoftDeletes;
use PHPUnit\Framework\TestCase;

class GenreUnitTest extends TestCase
{

    public function testFillable()
    {
        $arrExpected = ['name', 'is_active'];

        $genre = new Genre();
        $this->assertEquals($arrExpected, $genre->getFillable());
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
        $casts = ['id' => 'string', 'is_active' => 'boolean'];
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
