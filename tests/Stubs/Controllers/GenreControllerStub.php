<?php

namespace Tests\Stubs\Controllers;

use App\Http\Controllers\Api\BasicCrudController;
use Tests\Stubs\Models\GenreStub;

class GenreControllerStub extends BasicCrudController
{
    protected function model()
    {
        return GenreStub::class;
    }

    protected function rulesStore()
    {
        return [
            'name' => 'required|max:255'
        ];
    }

    protected function rulesUpdate()
    {
        return [
            'name' => 'required|max:255'
        ];
    }

    public function show($id)
    {
        return $this->findOrFail($id);
    }
}
