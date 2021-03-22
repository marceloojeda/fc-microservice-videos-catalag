<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\CastMember;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use Tests\Traits\TestValidations;

class CastMemberControllerTest extends TestCase
{
    use DatabaseMigrations, TestValidations;

    private $castMember;

    public function setUp(): void
    {
        parent::setUp();
        $this->castMember = factory(CastMember::class)->create(['type' => CastMember::MEMBER_ACTOR]);
        $this->castMember->refresh();
    }

    public function testIndex()
    {
        $response = $this->get(route('cast_members.index'));

        $response
            ->assertStatus(200)
            ->assertJson([$this->castMember->toArray()]);
    }

    public function testShow()
    {
        $response = $this->get(route('cast_members.show', ['cast_member' => $this->castMember->id]));

        $response
            ->assertStatus(200)
            ->assertJson($this->castMember->toArray());
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

        $data = ['type' => ''];
        $this->assertInvalidationFieldsInUpdateAction($data, 'required');

        // $data = ['type' => 'b'];
        // $this->assertInvalidationFieldsInStoreAction($data, 'in', CastMember::typesMembers());

        // $data = ['type' => 99];
        // $this->assertInvalidationFieldsInStoreAction($data, 'in', CastMember::typesMembers());
    }

    private function validationUpdate()
    {
        $data = ['name' => ''];
        $this->assertInvalidationFieldsInUpdateAction($data, 'required');

        $data = ['name' => str_repeat('a', 256)];
        $this->assertInvalidationFieldsInUpdateAction($data, 'max.string', ['max' => 255]);

        $data = ['is_active' => 'a'];
        $this->assertInvalidationFieldsInUpdateAction($data, 'boolean');

        $data = ['type' => ''];
        $this->assertInvalidationFieldsInUpdateAction($data, 'required');

        // $data = ['is_active' => 'b'];
        // $this->assertInvalidationFieldsInStoreAction($data, 'digits_between', CastMember::typesMembers());

        // $data = ['is_active' => 99];
        // $this->assertInvalidationFieldsInStoreAction($data, 'digits_between', CastMember::typesMembers());
    }

    public function testStore()
    {
        $response = $this->json('POST', route('cast_members.store'), [
            'name' => 'test',
            'type' => CastMember::MEMBER_ACTOR
        ]);
        $id = $response->json('id');
        $castMember = CastMember::find($id);

        $response
            ->assertStatus(201)
            ->assertJson($castMember->toArray());
        $this->assertTrue($response->json('is_active'));

        $response
            ->assertJsonFragment(['is_active' => true]);

        //     $response = $this->json('POST', route('cast_members.store'), [
        //     'name' => 'test'
        // ]);
        // $id = $response->json('id');
        // $castMember = CastMember::find($id);
    }

    public function testUpdate()
    {
        $response = $this->json(
            'PUT',
            route(
                'cast_members.update',
                ['cast_member' => $this->castMember->id]
            ),
            [
                'name' => 'name_altered',
                'type' => CastMember::MEMBER_DIRECTOR,
                'is_active' => true
            ]
        );

        $castMember = CastMember::find($response->json('id'));
        $response
            ->assertStatus(200)
            ->assertJson($castMember->toArray())
            ->assertJsonFragment([
                'name' => 'name_altered',
                'type' => CastMember::MEMBER_DIRECTOR,
                'is_active' => true
            ]);


        $castMember = factory(CastMember::class)->create([
            'is_active' => false
        ]);
        $response = $this->json(
            'PUT',
            route(
                'cast_members.update',
                ['cast_member' => $castMember->id]
            ),
            [
                'name' => 'name_altered',
                'type' => CastMember::MEMBER_DIRECTOR,
                'is_active' => true
            ]
        );

        $castMember = CastMember::find($response->json('id'));
        $response
            ->assertJsonFragment(['is_active' => true]);
    }

    public function testDestroy()
    {
        $response = $this->json(
            'DELETE',
            route('cast_members.destroy', ['cast_member' => $this->castMember->id])
        );
        $response->assertNoContent();
        $this->assertNull(CastMember::find($this->castMember->id));
        $this->assertNotNull(CastMember::withTrashed()->find($this->castMember->id));
    }

    protected function routeStore()
    {
        return route('cast_members.store');
    }

    protected function routeUpdate()
    {
        return route('cast_members.update', ['cast_member' => $this->castMember->id]);
    }
}
