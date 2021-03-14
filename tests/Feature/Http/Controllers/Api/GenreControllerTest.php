<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Http\Controllers\Api\BasicCrudController;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use ReflectionClass;
use Tests\Stubs\Controllers\GenreControllerStub;
use Tests\Stubs\Models\GenreStub;
use Tests\TestCase;

class GenreControllerTest extends TestCase
{
    /** @var GenreControllerStub $controller */
    private $controller;
    /** @var GenreStub $controller */
    private $genre;

    protected function setUp(): void
    {
        parent::setUp();
        GenreStub::dropTable();
        GenreStub::createTable();
        $this->controller = new GenreControllerStub();
        $this->genre = GenreStub::create(['name' => 'test_name']);
    }

    protected function tearDown(): void
    {
        GenreStub::dropTable();
        parent::tearDown();
    }

    public function testIndex()
    {
        $result = $this->controller->index()->toArray();
        $this->assertEquals([$this->genre->toArray()], $result);
    }

    public function testInvalidationDataInStore()
    {
        $this->expectException(ValidationException::class);
        /** @var Request $request */
        $request = \Mockery::mock(Request::class);
        $request->shouldReceive('all')
            ->once()
            ->andReturn(['name' => '']);
        $this->controller->store($request);
    }

    public function testStore()
    {
        /** @var Request $request */
        $request = \Mockery::mock(Request::class);
        $request->shouldReceive('all')
            ->once()
            ->andReturn(['name' => 'test_store_function']);
        $obj = $this->controller->store($request);

        $this->assertEquals(
            GenreStub::where(['name' => 'test_store_function'])->firstOrFail()->toArray(),
            $obj->toArray()
        );
    }

    public function testIfFindOrFailFetchModel()
    {
        $reflectionClass = new ReflectionClass(BasicCrudController::class);
        $reflectionMethod = $reflectionClass->getMethod('findOrFail');
        $reflectionMethod->setAccessible(true);

        $result = $reflectionMethod->invokeArgs($this->controller, [$this->genre->id]);
        $this->assertInstanceOf(GenreStub::class, $result);
    }

    public function testIfFindOrThrowExceptionWhenIdInvalid()
    {
        $this->expectException(ModelNotFoundException::class);
        $reflectionClass = new ReflectionClass(BasicCrudController::class);
        $reflectionMethod = $reflectionClass->getMethod('findOrFail');
        $reflectionMethod->setAccessible(true);

        $result = $reflectionMethod->invokeArgs($this->controller, [0]);
        $this->assertInstanceOf(GenreStub::class, $result);
    }

    public function testShow()
    {
        $response = $this->controller->show($this->genre->id);

        $this->assertEquals(
            GenreStub::find(1)->toArray(),
            $response->toArray()
        );
    }

    public function testUpdate()
    {
        $request = \Mockery::mock(Request::class);
        $request->shouldReceive('all')
            ->once()
            ->andReturn(['name' => 'test_name_changed']);
        $obj = $this->controller->update($request, $this->genre->id);
        $this->assertEquals(
            GenreStub::find(1)->toArray(),
            $obj->toArray()
        );
    }

    public function testDestroy()
    {
        $response = $this->controller->destroy($this->genre->id);
        $this->createTestResponse($response)
            ->assertStatus(204);
        $this->assertCount(0, GenreStub::all());
    }
}
