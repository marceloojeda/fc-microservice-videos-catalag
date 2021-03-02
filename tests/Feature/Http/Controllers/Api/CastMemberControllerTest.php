<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Http\Controllers\Api\BasicCrudController;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use ReflectionClass;
use Tests\Stubs\Controllers\CastMemberControllerStub;
use Tests\Stubs\Models\CastMemberStub;
use Tests\TestCase;

class CastMemberControllerTest extends TestCase
{
    /** @var CastMemberControllerStub $controller */
    private $controller;
    /** @var CastMemberStub $controller */
    private $castMember;

    protected function setUp(): void
    {
        parent::setUp();
        CastMemberStub::dropTable();
        CastMemberStub::createTable();
        $this->controller = new CastMemberControllerStub();
        $this->castMember = CastMemberStub::create(['name' => 'test_name', 'type' => CastMemberStub::MEMBER_ACTOR]);
    }

    protected function tearDown(): void
    {
        CastMemberStub::dropTable();
        parent::tearDown();
    }

    public function testIndex()
    {
        $result = $this->controller->index()->toArray();
        $this->assertEquals([$this->castMember->toArray()], $result);
    }

    public function testInvalidationDataInStore()
    {
        $this->expectException(ValidationException::class);
        /** @var Request $request */
        $request = \Mockery::mock(Request::class);
        $request->shouldReceive('all')
            ->once()
            ->andReturn(['name' => null, 'type' => 'invalid_data']);
        $this->controller->store($request);
    }

    public function testStore()
    {
        /** @var Request $request */
        $request = \Mockery::mock(Request::class);
        $request->shouldReceive('all')
            ->once()
            ->andReturn(['name' => 'test_name', 'type' => CastMemberStub::MEMBER_DIRECTOR]);
        $obj = $this->controller->store($request);

        $this->assertEquals(
            CastMemberStub::where(['name' => 'test_name', 'type' => CastMemberStub::MEMBER_DIRECTOR])->firstOrFail()->toArray(),
            $obj->toArray()
        );
    }

    public function testIfFindOrFailFetchModel()
    {
        $reflectionClass = new ReflectionClass(BasicCrudController::class);
        $reflectionMethod = $reflectionClass->getMethod('findOrFail');
        $reflectionMethod->setAccessible(true);

        $result = $reflectionMethod->invokeArgs($this->controller, [$this->castMember->id]);
        $this->assertInstanceOf(CastMemberStub::class, $result);
    }

    public function testIfFindOrThrowExceptionWhenIdInvalid()
    {
        $this->expectException(ModelNotFoundException::class);
        $reflectionClass = new ReflectionClass(BasicCrudController::class);
        $reflectionMethod = $reflectionClass->getMethod('findOrFail');
        $reflectionMethod->setAccessible(true);

        $result = $reflectionMethod->invokeArgs($this->controller, [0]);
        $this->assertInstanceOf(CastMemberStub::class, $result);
    }

    public function testShow()
    {
        $response = $this->controller->show($this->castMember->id);

        $this->assertEquals(
            CastMemberStub::find(1)->toArray(),
            $response->toArray()
        );
    }

    public function testUpdate()
    {
        $request = \Mockery::mock(Request::class);
        $request->shouldReceive('all')
            ->once()
            ->andReturn(['name' => 'test_name_changed', 'type' => CastMemberStub::MEMBER_DIRECTOR]);
        $obj = $this->controller->update($request, $this->castMember->id);
        $this->assertEquals(
            CastMemberStub::find(1)->toArray(),
            $obj->toArray()
        );
    }

    public function testDestroy()
    {
        $response = $this->controller->destroy($this->castMember->id);
        $this->createTestResponse($response)
            ->assertStatus(204);
        $this->assertCount(0, CastMemberStub::all());
    }
}
