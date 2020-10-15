<?php

namespace Tests\Feature\Models;

use App\Models\Genre;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class GenreTest extends TestCase
{
    use DatabaseMigrations;

    public function testList()
    {
        factory(Genre::class, 1)->create();
        $genres = Genre::all();
        $this->assertCount(1, $genres);
        $genreKeys = array_keys($genres->first()->getAttributes());
        $this->assertEqualsCanonicalizing(
            [
                'id',
                'name',
                'is_active',
                'created_at',
                'updated_at',
                'deleted_at'
            ],
            $genreKeys
        );
    }

    public function testCreate() {
        $genre = Genre::create([
            'name' => 'teste1'
        ]);
        $genre->refresh();

        $this->assertTrue(\Ramsey\Uuid\Uuid::isValid($genre->id));
        $this->assertEquals('teste1', $genre->name);
        $this->assertTrue($genre->is_active);

        $genre = Genre::create([
            'name' => 'teste2',
            'is_active' => false
        ]);
        $this->assertFalse($genre->is_active);
    }

    public function testUpdate() {
        $genre = factory(Genre::class)->create([
            'name' => 'test3',
            'is_active' => false
        ])->first();

        $data = [
            'name' => 'name_updated',
            'is_active' => true
        ];

        $genre->update($data);

        foreach ($data as $key => $value) {
            $this->assertEquals($value, $genre->{$key});
        };
    }

    public function testDelete() {
        $genre = factory(Genre::class)->create()->first();
        $genre->refresh();

        $id = $genre->id;
        $this->assertTrue(\Ramsey\Uuid\Uuid::isValid($id));

        $genre->delete();

        $this->assertNull(Genre::first('id', $id));
    }
}
