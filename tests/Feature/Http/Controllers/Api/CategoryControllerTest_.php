<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\Category;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\TestResponse;
use Tests\TestCase;
use Tests\Traits\TestValidations;

class CategoryControllerTest_ extends TestCase
{
    use DatabaseMigrations, TestValidations;

    private $category;

    public function setUp(): void
    {
        parent::setUp();
        $this->category = factory(Category::class)->create();
        $this->category->refresh();
    }

    public function testIndex()
    {
        $response = $this->get(route('categories.index'));

        $response
            ->assertStatus(200)
            ->assertJson([$this->category->toArray()]);
    }

    public function testShow()
    {
        $response = $this->get(route('categories.show', ['category' => $this->category->id]));

        $response
            ->assertStatus(200)
            ->assertJson($this->category->toArray());
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
        $response = $this->json('POST', route('categories.store'), [
            'name' => 'test'
        ]);
        $id = $response->json('id');
        $category = Category::find($id);

        $response
            ->assertStatus(201)
            ->assertJson($category->toArray());
        $this->assertTrue($response->json('is_active'));
        $this->assertNull($response->json('description'));

        $response = $this->json('POST', route('categories.store'), [
            'name' => 'test',
            'description' => 'description',
            'is_active' => false
        ]);
        $id = $response->json('id');
        $category = Category::find($id);

        $response
            ->assertJsonFragment([
                'description' => $response->json('description'),
                'is_active' => false
            ]);
    }

    public function testUpdate()
    {
        $category = factory(Category::class)->create([
            'is_active' => false
        ]);
        $response = $this->json(
            'PUT',
            route(
                'categories.update',
                ['category' => $category->id]
            ),
            [
                'name' => 'name_altered',
                'description' => 'description',
                'is_active' => true
            ]
        );

        $category = Category::find($response->json('id'));
        $response
            ->assertStatus(200)
            ->assertJson($category->toArray())
            ->assertJsonFragment([
                'name' => 'name_altered',
                'description' => 'description',
                'is_active' => true
            ]);


        $category = factory(Category::class)->create([
            'is_active' => false
        ]);
        $response = $this->json(
            'PUT',
            route(
                'categories.update',
                ['category' => $category->id]
            ),
            [
                'name' => 'name_altered',
                'description' => null,
                'is_active' => true
            ]
        );

        $category = Category::find($response->json('id'));
        $this->assertNull($response->json('description'));
    }

    public function testDestroy()
    {
        $response = $this->json(
            'DELETE',
            route('categories.destroy', ['category' => $this->category->id])
        );
        $response->assertNoContent();
        $this->assertNull(Category::find($this->category->id));
        $this->assertNotNull(Category::withTrashed()->find($this->category->id));
    }

    protected function routeStore()
    {
        return route('categories.store');
    }

    protected function routeUpdate()
    {
        return route('categories.update', ['category' => $this->category->id]);
    }
}
