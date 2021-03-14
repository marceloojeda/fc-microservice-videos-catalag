<?php

use App\Models\CastMember;
use Illuminate\Database\Seeder;

class CastMemberSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(\App\Models\CastMember::class)->create(['type' => CastMember::MEMBER_DIRECTOR]);
        factory(\App\Models\CastMember::class)->create(['type' => CastMember::MEMBER_ACTOR]);
    }
}
