<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            CategorySeeder::class,
            GenreSeeder::class,
            CastMemberSeeder::class,
            VideosSeeder::class
        ]);
    }
}
