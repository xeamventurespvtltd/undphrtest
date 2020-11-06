<?php

use Illuminate\Database\Seeder;
use App\Department;

class DepartmentsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement("INSERT INTO `departments` (`id`, `name`) VALUES
        (1, 'Admin'),
        (2, 'Nort'),
        (3, 'South'),
        (4, 'East'),
        (5, 'West');");
    }
}
