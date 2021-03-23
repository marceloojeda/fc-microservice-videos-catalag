<?php

namespace Tests\Feature\Models;

use App\Models\CastMember;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class CastMemberTest extends TestCase
{
    use DatabaseMigrations;

    public function testList()
    {
        factory(CastMember::class, 1)->create();
        $castMember = CastMember::all();
        $this->assertCount(1, $castMember);
        $castMemberKeys = array_keys($castMember->first()->getAttributes());
        $this->assertEqualsCanonicalizing(
            [
                'id',
                'name',
                'type',
                'is_active',
                'created_at',
                'updated_at',
                'deleted_at'
            ],
            $castMemberKeys
        );
    }

    public function testCreate() {
        $castMember = CastMember::create([
            'name' => 'teste1',
            'type' => CastMember::MEMBER_ACTOR
        ]);
        $castMember->refresh();

        $this->assertTrue(\Ramsey\Uuid\Uuid::isValid($castMember->id));
        $this->assertEquals('teste1', $castMember->name);
        $this->assertTrue($castMember->is_active);

        $castMember = CastMember::create([
            'name' => 'teste2',
            'type' => CastMember::MEMBER_DIRECTOR,
            'is_active' => false
        ]);
        $this->assertFalse($castMember->is_active);
    }

    public function testUpdate() {
        $castMember = factory(CastMember::class)->create([
            'name' => 'test3',
            'type' => CastMember::MEMBER_DIRECTOR,
            'is_active' => false
        ])->first();

        $data = [
            'name' => 'name_updated',
            'type' => CastMember::MEMBER_ACTOR,
            'is_active' => true
        ];

        $castMember->update($data);

        foreach ($data as $key => $value) {
            $this->assertEquals($value, $castMember->{$key});
        };
    }

    public function testDelete() {
        $castMember = factory(CastMember::class)->create()->first();
        $castMember->refresh();

        $id = $castMember->id;
        $this->assertTrue(\Ramsey\Uuid\Uuid::isValid($id));

        $castMember->delete();

        $this->assertNull(CastMember::first('id', $id));
    }
}
