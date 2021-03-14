<?php

namespace Tests\Stubs\Controllers;

use App\Http\Controllers\Api\BasicCrudController;
use Tests\Stubs\Models\CastMemberStub;

class CastMemberControllerStub extends BasicCrudController
{
    protected function model()
    {
        return CastMemberStub::class;
    }

    protected function rulesStore()
    {
        return [
            'name' => 'required|max:255',
            'type' => 'required|digits_between:' . implode(',', CastMemberStub::typesMembers()),
        ];
    }

    protected function rulesUpdate()
    {
        return [
            'name' => 'required|max:255',
            'type' => 'required|digits_between:' . implode(',', CastMemberStub::typesMembers()),
        ];
    }

    public function show($id)
    {
        return $this->findOrFail($id);
    }
}
