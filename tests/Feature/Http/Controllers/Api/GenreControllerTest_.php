<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\Genre;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\TestResponse;
use Tests\TestCase;
use Tests\Traits\TestSaves;
use Tests\Traits\TestValidations;

class GenreControllerTest_ extends TestCase
{
    use DatabaseMigrations, TestValidations, TestSaves;

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
        $data = ['name' => 'test'];
        $response = $this->assertStore($data, $data + ['is_active' => true, 'deleted_at' => null]);

        $data = [
            'name' => 'test3',
            'is_active' => false
        ];
        $response = $this->assertStore($data, $data, ['name' => 'test3', 'is_active' => false]);
        $response->assertJsonStructure([
            'created_at',
            'updated_at'
        ]);
    }

    public function testUpdate()
    {
        $this->genre = factory(Genre::class)->create([
            'is_active' => false
        ]);
        $data = [
            'name' => 'name_altered',
            'is_active' => true
        ];
        $response = $this->assertUpdate($data, $data + ['deleted_at' => null], $data + ['deleted_at' => null]);
        $response->assertJsonStructure([
            'created_at',
            'updated_at'
        ]);
    }

    public function testDestroy() {
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

    protected function model() {
        return Genre::class;
    }
}
