<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Http\Controllers\Api\GenreController;
use App\Models\Category;
use App\Models\Genre;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Http\Request;
use Tests\Exceptions\TestException;
use Tests\TestCase;
use Tests\Traits\TestSaves;
use Tests\Traits\TestValidations;

class GenreControllerTest extends TestCase
{
    use DatabaseMigrations, TestValidations, TestSaves;

    private $genre;
    private $category;
    private $sendData;

    public function setUp(): void
    {
        parent::setUp();
        $this->genre = factory(Genre::class)->create();
        $this->category = factory(Category::class)->create();
        $this->genre->refresh();
        $this->sendData = [
            'name' => 'genre name'
        ];
    }

    protected function model()
    {
        return Genre::class;
    }

    protected function routeStore()
    {
        return route('genres.store');
    }

    protected function routeUpdate()
    {
        return route('genres.update', ['genre' => $this->genre->id]);
    }

    public function testInvalidationData()
    {
        $data = ['name' => '', 'categories_id' => ''];
        $this->assertInvalidationFieldsInStoreAction($data, 'required');
        $this->assertInvalidationFieldsInUpdateAction($data, 'required');

        $data = ['name' => str_repeat('a', 256)];
        $this->assertInvalidationFieldsInStoreAction($data, 'max.string', ['max' => 255]);
        $this->assertInvalidationFieldsInUpdateAction($data, 'max.string', ['max' => 255]);

        $data = ['is_active' => 'a'];
        $this->assertInvalidationFieldsInStoreAction($data, 'boolean');
        $this->assertInvalidationFieldsInUpdateAction($data, 'boolean');

        $data = ['categories_id' => 'a'];
        $this->assertInvalidationFieldsInStoreAction($data, 'array');
        $this->assertInvalidationFieldsInUpdateAction($data, 'array');

        $data = ['categories_id' => [100]];
        $this->assertInvalidationFieldsInStoreAction($data, 'exists');
        $this->assertInvalidationFieldsInUpdateAction($data, 'exists');

        $category = factory(Category::class)->create();
        $category->delete();
        $data = ['categories_id' => [$category->id]];
        $this->assertInvalidationFieldsInStoreAction($data, 'exists');
        $this->assertInvalidationFieldsInUpdateAction($data, 'exists');
    }

    public function testInvalidationMax()
    {
        $data = [ 'name' => str_repeat('a', 256) ];
        $this->assertInvalidationFieldsInStoreAction($data, 'max.string', ['max' => 255]);
        $this->assertInvalidationFieldsInUpdateAction($data, 'max.string', ['max' => 255]);
    }

    public function testInvalidationCategoriesIdField()
    {
        $this->assertInvalidationArray(['categories_id' => 'a']);
        $this->assertInvalidationExists(['categories_id' => [100]]);
    }

    private function assertInvalidationArray(array $data)
    {
        $this->assertInvalidationFieldsInStoreAction($data, 'array');
        $this->assertInvalidationFieldsInUpdateAction($data, 'array');
    }

    private function assertInvalidationExists(array $data)
    {
        $this->assertInvalidationFieldsInStoreAction($data, 'exists');
        $this->assertInvalidationFieldsInUpdateAction($data, 'exists');
    }

    public function testIndex()
    {
        $response = $this->get(route('genres.index'));

        $response
            ->assertStatus(200)
            ->assertJson([$this->genre->toArray()]);
    }

    public function testShow()
    {
        $response = $this->get(route('genres.show', ['genre' => $this->genre->id]));

        $response
            ->assertStatus(200)
            ->assertJson($this->genre->toArray());
    }

    public function testSave()
    {
        $response = $this->assertStore(
            $this->sendData + ['categories_id' => [$this->category->id]],
            $this->sendData + ['deleted_at' => null, 'is_active' => true]
        );
        $response->assertJsonStructure([
            'created_at',
            'updated_at'
        ]);
        $this->assertHasCategory($response->json('id'), $this->category->id);

        $response = $this->assertUpdate(
            $this->sendData + [
                'categories_id' => [$this->category->id],
                'is_active' => false
            ],
            $this->sendData + [
                'deleted_at' => null,
                'is_active' => false
            ]
        );
        $response->assertJsonStructure([
            'created_at',
            'updated_at'
        ]);
        $this->assertHasCategory($response->json('id'), $this->category->id);
    }

    private function assertHasCategory($genreId, $categoryId)
    {
        $this->assertDatabaseHas('category_genre', [
            'genre_id' => $genreId,
            'category_id' => $categoryId
        ]);
    }

    public function testDestroy()
    {
        $response = $this->json(
            'DELETE',
            route('genres.destroy', ['genre' => $this->genre->id])
        );
        $response->assertNoContent();
        $this->assertNull(Genre::find($this->genre->id));
        $this->assertNotNull(Genre::withTrashed()->find($this->genre->id));
    }


    public function testRollbackStore()
    {
        $controller = \Mockery::mock(GenreController::class)
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

        $hasError = false;
        try {
            $controller->store($request);
        } catch (TestException $e) {
            $this->assertCount(1, Genre::all());
            $hasError = true;
        }

        $this->assertTrue($hasError);
    }

    public function testRollbackUpdate()
    {
        $controller = \Mockery::mock(GenreController::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $controller
            ->shouldReceive('findOrFail')
            ->withAnyArgs()
            ->andReturn($this->genre);

        $controller
            ->shouldReceive('validate')
            ->withAnyArgs()
            ->andReturn($this->sendData);

        $controller
            ->shouldReceive('rulesUpdate')
            ->withAnyArgs()
            ->andReturn([]);

        $controller->shouldReceive('handleRelations')
            ->once()
            ->andThrow(new TestException());

        $request = \Mockery::mock(Request::class);

        $hasError = false;
        try {
            $controller->update($request, $this->genre->id);
        } catch (TestException $e) {
            $this->assertCount(1, Genre::all());
            $hasError = true;
        }

        $this->assertTrue($hasError);
    }

    public function testSyncCategories()
    {
        $categoriesId = factory(Category::class, 3)->create()->pluck('id')->toArray();

        $sendData = [
            'name' => 'teste',
            'categories_id' => [$categoriesId[0]]
        ];
        $response = $this->json('POST', $this->routeStore(), $sendData);
        $this->assertDatabaseHas('category_genre', [
            'category_id' => $categoriesId[0],
            'genre_id' => $response->json('id')
        ]);

        $sendData = [
            'name' => 'teste',
            'categories_id' => [$categoriesId[1], $categoriesId[2]]
        ];
        $response = $this->json('PUT',
            route('genres.update', ['genre' => $response->json('id')]),
            $sendData
        );
        $this->assertDatabaseMissing('category_genre', [
            'category_id' => $categoriesId[0],
            'genre_id' => $response->json('id')
        ]);
        $this->assertDatabaseHas('category_genre', [
            'category_id' => $categoriesId[1],
            'genre_id' => $response->json('id')
        ]);
        $this->assertDatabaseHas('category_genre', [
            'category_id' => $categoriesId[2],
            'genre_id' => $response->json('id')
        ]);
    }
}
