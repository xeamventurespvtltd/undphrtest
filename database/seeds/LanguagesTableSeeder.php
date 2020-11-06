<?php

use Illuminate\Database\Seeder;
use App\Language;

class LanguagesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement("INSERT INTO `languages` (`id`, `name`) VALUES
        (1, 'Urdu'),
        (2, 'English'),
        (3, 'Punjabi'),
        (4, 'Marathi'),
        (5, 'Bengali'),
        (6, 'Assammesse'),
        (7, 'Hindi'),
        (8, 'Oriya'),
        (9, 'Gujarati'),
        (10, 'Telugu'),
        (11, 'Tamil'),
        (12, 'Kannada'),
        (13, 'Malayalam'),
        (14, 'Maithili'),
        (15, 'Santali'),
        (16, 'Kashmiri'),
        (17, 'Sindhi'),
        (18, 'Konkani'),
        (19, 'Dogri'),
        (20, 'Manipuri'),
        (21, 'Khasi'),
        (22, 'Mundari'),
        (23, 'Bodo'),
        (24, 'Kurukh');");
    }
}
