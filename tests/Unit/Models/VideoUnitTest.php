<?php

namespace Tests\Unit\Models;

use App\Models\Video;
use App\Models\Traits\Uuid;
use Illuminate\Database\Eloquent\SoftDeletes;
use PHPUnit\Framework\TestCase;

class VideoUnitTest extends TestCase
{

    public function testFillable()
    {
        $arrExpected = ['title', 'description', 'year_launched', 'opened', 'rating', 'duration'];

        $video = new Video();
        $this->assertEquals($arrExpected, $video->getFillable());
    }

    public function testIfUseTraits() {
        $traits = [
            SoftDeletes::class,
            Uuid::class
        ];
        $video = array_keys(class_uses(Video::class));
        $this->assertEquals($traits, $video);
    }

    public function testCasts() {
        $casts = [
            'id' => 'string',
            'year_launched' => 'integer',
            'opened' => 'boolean',
            'duration' => 'integer'
        ];
        $video = new Video();
        $this->assertEquals($casts, $video->getCasts());
    }

    public function testIncrementing() {
        $video = new Video();
        $this->assertFalse($video->incrementing);
    }

    public function testDatesAttributes() {
        $dates = ['deleted_at', 'created_at', 'updated_at'];
        $video = new Video();
        foreach ($dates as $date) {
            $this->assertContains($date, $video->getDates());
        }
        $this->assertCount(sizeof($dates), $video->getDates());
    }
}
