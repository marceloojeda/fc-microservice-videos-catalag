<?php

namespace App\Http\Controllers\Api;

use App\Models\CastMember;

class CastMemberController extends BasicCrudController
{
    protected function model()
    {
        return CastMember::class;
    }

    protected function rulesStore()
    {
        return [
            'name' => 'required|max:255',
            'type' => 'required|digits_between:' . implode(',', CastMember::typesMembers()),
            'is_active' => 'boolean'
        ];
    }

    protected function rulesUpdate()
    {
        return [
            'name' => 'required|max:255',
            'type' => 'required|digits_between:' . implode(',', CastMember::typesMembers()),
            'is_active' => 'boolean'
        ];
    }
}
