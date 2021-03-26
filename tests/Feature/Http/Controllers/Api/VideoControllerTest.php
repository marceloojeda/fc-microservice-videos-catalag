<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\Video;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use Tests\Traits\TestSaves;
use Tests\Traits\TestValidations;

class VideoControllerTest extends TestCase
{
    use DatabaseMigrations, TestValidations, TestSaves;

    private $video;
    private $sendData;

    public function setUp(): void
    {
        parent::setUp();
        $this->video = factory(Video::class)->create();
        $this->video->refresh();
        $this->sendData = [
            'title' => 'title',
            'description' => 'description',
            'year_launched' => 1995,
            'rating' => Video::RATING_LIST[rand(0, sizeof(Video::RATING_LIST) - 1)],
            'duration' => 2555
        ];
    }

    public function testIndex()
    {
        $response = $this->get(route('videos.index'));

        $response
            ->assertStatus(200)
            ->assertJson([$this->video->toArray()]);
    }

    public function testShow()
    {
        $response = $this->get(route('videos.show', ['video' => $this->video->id]));

        $response
            ->assertStatus(200)
            ->assertJson($this->video->toArray());
    }

    public function testInvalidationRequired()
    {
        $data = [
            'title' => '',
            'description' => '',
            'year_launched' => '',
            'rating' => '',
            'duration' => ''
        ];
        $this->assertInvalidationFieldsInStoreAction($data, 'required');
        $this->assertInvalidationFieldsInUpdateAction($data, 'required');
    }

    public function testInvalidationMax()
    {
        $data = [ 'title' => str_repeat('a', 256) ];
        $this->assertInvalidationFieldsInStoreAction($data, 'max.string', ['max' => 255]);
        $this->assertInvalidationFieldsInUpdateAction($data, 'max.string', ['max' => 255]);
    }

    public function testInvalidationInteger()
    {
        $data = [ 'duration' => 'a' ];
        $this->assertInvalidationFieldsInStoreAction($data, 'integer');
        $this->assertInvalidationFieldsInUpdateAction($data, 'integer');
    }

    public function testInvalidationBoolean()
    {
        $data = [ 'opened' => 'a' ];
        $this->assertInvalidationFieldsInStoreAction($data, 'boolean');
        $this->assertInvalidationFieldsInUpdateAction($data, 'boolean');
    }

    public function testInvalidationYearLaunchedField()
    {
        $data = [ 'year_launched' => 'a' ];
        $this->assertInvalidationFieldsInStoreAction($data, 'date_format', ['format' => 'Y']);
        $this->assertInvalidationFieldsInUpdateAction($data, 'date_format', ['format' => 'Y']);
    }

    public function testInvalidationRatingField()
    {
        $data = [ 'rating' => 0 ];
        $this->assertInvalidationFieldsInStoreAction($data, 'in');
        $this->assertInvalidationFieldsInUpdateAction($data, 'in');
    }

    public function testSave()
    {
        $data = [
            [
                'send_data' => $this->sendData,
                'test_data' => $this->sendData + ['opened' => false]
            ],
            [
                'send_data' => $this->sendData + ['opened' => true],
                'test_data' => $this->sendData + ['opened' => true]
            ],
            [
                'send_data' => $this->sendData + ['rating' => Video::RATING_LIST[3]],
                'test_data' => $this->sendData + ['rating' => Video::RATING_LIST[3]]
            ]
        ];
        foreach($data as $key => $value) {
            $response = $this->assertStore(
                $value['send_data'],
                $value['test_data'] + ['deleted_at' => null]
            );
            $response->assertJsonStructure([
                'created_at',
                'updated_at'
            ]);

            $response = $this->assertUpdate(
                $value['send_data'],
                $value['test_data'] + ['deleted_at' => null]
            );
            $response->assertJsonStructure([
                'created_at',
                'updated_at'
            ]);
        }
        $response = $this->json('POST', route('videos.store'), $this->sendData);
        $id = $response->json('id');
        $video = Video::find($id);

        $response
            ->assertStatus(201)
            ->assertJson($video->toArray());
        $this->assertFalse($response->json('opened'));
    }

    public function testDestroy()
    {
        $response = $this->json(
            'DELETE',
            route('videos.destroy', ['video' => $this->video->id])
        );
        $response->assertNoContent();
        $this->assertNull(Video::find($this->video->id));
        $this->assertNotNull(Video::withTrashed()->find($this->video->id));
    }

    protected function model()
    {
        return Video::class;
    }

    protected function routeStore()
    {
        return route('videos.store');
    }

    protected function routeUpdate()
    {
        return route('videos.update', ['video' => $this->video->id]);
    }
}
