<?php

use Illuminate\Database\Seeder;

class StatesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement("INSERT INTO `states` (`id`, `country_id`, `name`, `created_at`, `updated_at`) VALUES
                (1, 1, 'Andaman and Nicobar Islands', NULL, NULL),
                (2, 1, 'Andhra Pradesh', NULL, NULL),
                (3, 1, 'Arunachal Pradesh', NULL, NULL),
                (4, 1, 'Assam', NULL, NULL),
                (5, 1, 'Bihar', NULL, NULL),
                (6, 1, 'Chandigarh', NULL, NULL),
                (7, 1, 'Chhattisgarh', NULL, NULL),
                (8, 1, 'Dadra and Nagar Haveli', NULL, NULL),
                (9, 1, 'Daman and Diu', NULL, NULL),
                (10, 1, 'Delhi', NULL, NULL),
                (11, 1, 'Goa', NULL, NULL),
                (12, 1, 'Gujarat', NULL, NULL),
                (13, 1, 'Haryana', NULL, NULL),
                (14, 1, 'Himachal Pradesh', NULL, NULL),
                (15, 1, 'Jammu and Kashmir', NULL, NULL),
                (16, 1, 'Jharkhand', NULL, NULL),
                (17, 1, 'Karnataka', NULL, NULL),
                (18, 1, 'Kerala', NULL, NULL),
                (19, 1, 'Lakshadweep', NULL, NULL),
                (20, 1, 'Madhya Pradesh', NULL, NULL),
                (21, 1, 'Maharashtra', NULL, NULL),
                (22, 1, 'Manipur', NULL, NULL),
                (23, 1, 'Meghalaya', NULL, NULL),
                (24, 1, 'Mizoram', NULL, NULL),
                (25, 1, 'Nagaland', NULL, NULL),
                (26, 1, 'Orissa', NULL, NULL),
                (27, 1, 'Punducherry', NULL, NULL),
                (28, 1, 'Punjab', NULL, NULL),
                (29, 1, 'Rajasthan', NULL, NULL),
                (30, 1, 'Sikkim', NULL, NULL),
                (31, 1, 'Tamil Nadu', NULL, NULL),
                (32, 1, 'Telangana', NULL, NULL),
                (33, 1, 'Tripura', NULL, NULL),
                (34, 1, 'Uttar Pradesh', NULL, NULL),
                (35, 1, 'Uttarakhand', NULL, NULL),
                (36, 1, 'West Bengal', NULL, NULL)");
      
    }
}
