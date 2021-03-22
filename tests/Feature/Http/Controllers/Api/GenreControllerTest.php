<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\Genre;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use Tests\Traits\TestValidations;

class GenreControllerTest extends TestCase
{
    use DatabaseMigrations, TestValidations;

    private $genre;

    public function setUp(): void
    {
        parent::setUp();
        $this->genre = factory(Genre::class)->create();
        $this->genre->refresh();
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

    public function testValidationData()
    {
        $this->validationStore();
        $this->validationUpdate();
    }

    private function validationStore()
    {
        $data = ['name' => ''];
        $this->assertInvalidationFieldsInStoreAction($data, 'required');

        $data = ['name' => str_repeat('a', 256)];
        $this->assertInvalidationFieldsInStoreAction($data, 'max.string', ['max' => 255]);

        $data = ['is_active' => 'a'];
        $this->assertInvalidationFieldsInStoreAction($data, 'boolean');
    }

    private function validationUpdate()
    {
        $data = ['name' => ''];
        $this->assertInvalidationFieldsInUpdateAction($data, 'required');

        $data = ['name' => str_repeat('a', 256)];
        $this->assertInvalidationFieldsInUpdateAction($data, 'max.string', ['max' => 255]);

        $data = ['is_active' => 'a'];
        $this->assertInvalidationFieldsInUpdateAction($data, 'boolean');
    }

    public function testStore()
    {
        $response = $this->json('POST', route('genres.store'), [
            'name' => 'test'
        ]);
        $id = $response->json('id');
        $genre = Genre::find($id);

        $response
            ->assertStatus(201)
            ->assertJson($genre->toArray());
        $this->assertTrue($response->json('is_active'));

        $response = $this->json('POST', route('genres.store'), [
            'name' => 'test'
        ]);
        $id = $response->json('id');
        $genre = Genre::find($id);

        $response
            ->assertJsonFragment(['is_active' => true]);
    }

    public function testUpdate()
    {
        $response = $this->json(
            'PUT',
            route(
                'genres.update',
                ['genre' => $this->genre->id]
            ),
            [
                'name' => 'name_altered',
                'is_active' => true
            ]
        );

        $genre = Genre::find($response->json('id'));
        $response
            ->assertStatus(200)
            ->assertJson($genre->toArray())
            ->assertJsonFragment([
                'name' => 'name_altered',
                'is_active' => true
            ]);


        $genre = factory(Genre::class)->create([
            'is_active' => false
        ]);
        $response = $this->json(
            'PUT',
            route(
                'genres.update',
                ['genre' => $genre->id]
            ),
            [
                'name' => 'name_altered',
                'is_active' => true
            ]
        );

        $genre = Genre::find($response->json('id'));
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

    protected function routeStore()
    {
        return route('genres.store');
    }

    protected function routeUpdate()
    {
        return route('genres.update', ['genre' => $this->genre->id]);
    }
}
