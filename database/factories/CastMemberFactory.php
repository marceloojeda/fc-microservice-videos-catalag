<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\CastMember;
use Faker\Generator as Faker;

$factory->define(CastMember::class, function (Faker $faker) {
    return [
        'name' => $faker->colorName,
        'type' => rand(CastMember::typesMembers()),
        'is_active' => rand(true, false)
    ];
});
