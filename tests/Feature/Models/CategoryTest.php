<?php

namespace Tests\Feature\Models;

use App\Models\Category;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use DatabaseMigrations;

    public function testList()
    {
        factory(Category::class, 1)->create();
        $categories = Category::all();
        $this->assertCount(1, $categories);
        $categoryKeys = array_keys($categories->first()->getAttributes());
        $this->assertEqualsCanonicalizing(
            [
                'id',
                'name',
                'description',
                'is_active',
                'created_at',
                'updated_at',
                'deleted_at'
            ],
            $categoryKeys
        );
    }

    public function testCreate() {
        $category = Category::create([
            'name' => 'teste1'
        ]);
        $category->refresh();

        $this->assertTrue(\Ramsey\Uuid\Uuid::isValid($category->id));
        $this->assertEquals('teste1', $category->name);
        $this->assertNull($category->description);
        $this->assertTrue($category->is_active);

        $category = Category::create([
            'name' => 'teste2',
            'description' => 'description_test'
        ]);
        $this->assertEquals('description_test', $category->description);

        $category = Category::create([
            'name' => 'teste3',
            'is_active' => false
        ]);
        $this->assertFalse($category->is_active);
    }

    public function testUpdate() {
        $category = factory(Category::class)->create([
            'description' => 'test_description',
            'is_active' => false
        ])->first();

        $data = [
            'name' => 'name_updated',
            'description' => 'test_description_updated',
            'is_active' => true
        ];

        $category->update($data);

        foreach ($data as $key => $value) {
            $this->assertEquals($value, $category->{$key});
        };
    }

    public function testDelete() {
        $category = factory(Category::class)->create()->first();
        $category->refresh();

        $id = $category->id;
        $this->assertTrue(\Ramsey\Uuid\Uuid::isValid($id));

        $category->delete();

        $this->assertNull(Category::first('id', $id));
    }
}
