<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Http\Controllers\Api\VideoController;
use App\Models\Category;
use App\Models\Genre;
use App\Models\Video;
use Exception;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Http\Request;
use Tests\Exceptions\TestException;
use Tests\TestCase;
use Tests\Traits\TestSaves;
use Tests\Traits\TestValidations;

class VideoControllerTest extends TestCase
{
    use DatabaseMigrations, TestValidations, TestSaves;

    private $video;
    private $sendData;
    private $category;
    private $genre;

    public function setUp(): void
    {
        parent::setUp();
        $this->category = factory(Category::class)->create();
        $this->genre = factory(Genre::class)->create();
        $this->video = factory(Video::class)->create();
        $this->video->refresh();
        $this->sendData = [
            'title' => 'title',
            'description' => 'description',
            'year_launched' => 1995,
            'rating' => Video::RATING_LIST[rand(0, sizeof(Video::RATING_LIST) - 1)],
            'duration' => 2555,
            'opened' => false
        ];
    }

    public function testInvalidationRequired()
    {
        $data = [
            'title' => '',
            'description' => '',
            'year_launched' => '',
            'rating' => '',
            'duration' => '',
            'categories_id' => '',
            'genres_id' => ''
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

    protected function assertInvalidationArray(array $data)
    {
        $this->assertInvalidationFieldsInStoreAction($data, 'array');
        $this->assertInvalidationFieldsInUpdateAction($data, 'array');
    }

    protected function assertInvalidationExists(array $data)
    {
        $this->assertInvalidationFieldsInStoreAction($data, 'exists');
        $this->assertInvalidationFieldsInUpdateAction($data, 'exists');
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

    public function testInvalidationCategoriesIdField()
    {
        $this->assertInvalidationArray(['categories_id' => 'a']);
        $this->assertInvalidationExists(['categories_id' => [100]]);
    }

    public function testInvalidationGenresIdField()
    {
        $this->assertInvalidationArray(['genres_id' => 'a']);
        $this->assertInvalidationExists(['genres_id' => [100]]);
    }

    public function testSave()
    {
        $data = [
            [
                'send_data' => $this->sendData + [
                    'categories_id' => [$this->category->id],
                    'genres_id' => [$this->genre->id]
                ],
                'test_data' => $this->sendData + ['opened' => false]
            ],
            [
                'send_data' => $this->sendData + [
                    'opened' => true,
                    'categories_id' => [$this->category->id],
                    'genres_id' => [$this->genre->id]
                ],
                'test_data' => $this->sendData + ['opened' => true]
            ],
            [
                'send_data' => $this->sendData + [
                    'rating' => Video::RATING_LIST[3],
                    'categories_id' => [$this->category->id],
                    'genres_id' => [$this->genre->id]
                ],
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

    public function testRollback()
    {
        $controller = \Mockery::mock(VideoController::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $controller
            ->shouldReceive('validate')
            ->withAnyArgs()
            ->andReturn($this->sendData);

        $controller
            ->shouldReceive('rulesStore')
            ->withAnyArgs()
            ->andReturn([]);

        $controller->shouldReceive('handleRelations')
            ->once()
            ->andThrow(new TestException());

        $request = \Mockery::mock(Request::class);

        try {
            $controller->store($request);
        } catch (TestException $e) {
            $this->assertCount(1, Video::all());
        }
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
