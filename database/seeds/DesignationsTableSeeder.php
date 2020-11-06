<?php

use Illuminate\Database\Seeder;

class DesignationsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement("INSERT INTO `designations` (`id`, `name`, `short_name`, `isactive`, `hierarchy`, `band_id`, `sort_order`, `created_at`, `updated_at`) VALUES
        (1, 'NPO', 'NPO', 1, 1, 1, 0, NULL, NULL),
        (2, 'SPO', 'SPO', 1, 2, 3, 0, NULL, NULL),
        (3, 'PO', 'PO', 1, 3, 3, 0, NULL, NULL),
        (4, 'VCCM', 'VCCM', 1, 4, 3, 0, NULL, NULL)");
    }
}
