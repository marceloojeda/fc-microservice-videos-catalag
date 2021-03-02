<?php

namespace App\Http\Controllers\Api;

use App\Models\Category;

class CategoryController extends BasicCrudController
{
    protected function model()
    {
        return Category::class;
    }

    protected function rulesStore()
    {
        return [
            'name' => 'required|max:255',
            'is_active' => 'boolean'
        ];
    }

    protected function rulesUpdate()
    {
        return [
            'id' => 'required',
            'name' => 'required|max:255',
            'is_active' => 'boolean'
        ];
    }
}
