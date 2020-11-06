<?php

use Illuminate\Database\Seeder;
use App\Bank;

class BanksTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement("INSERT INTO `banks` (`id`, `name`) VALUES
        (1, 'AXIS'),
        (2, 'Bank of India'),
        (3, 'HDFC'),
        (4, 'INDIAN BANK'),
        (5, 'OBC'),
        (6, 'Punjab & Sind Bank'),
        (7, 'PNB'),
        (8, 'State Bank of India'),
        (9, 'State Bank of Patiala'),
        (10, 'ALLAHABAD BANK'),
        (11, 'ANDHRA BANK'),
        (12, 'Bank of Baroda'),
        (13, 'Bank of Maharastra'),
        (14, 'Canara Bank'),
        (15, 'Central Bank of India'),
        (16, 'CORPORATION BANK'),
        (17, 'Dena'),
        (18, 'FEDERAL BANK'),
        (19, 'ICICI'),
        (20, 'IDBI Bank'),
        (21, 'Indian Oversea Bank'),
        (22, 'Syndicate'),
        (23, 'UCO BANK'),
        (24, 'UNION BANK OF INDIA'),
        (25, 'UNITED BANK OF INDIA'),
        (26, 'Vijaya Bank'),
        (27, 'Indusind Bank'),
        (28, 'IDBI'),
        (29, 'Yes Bank'),
        (30, 'Kotak Mahindra bank'),
        (31, 'Bank of Maharashtra'),
        (32, 'Bank Of America');");
    }
}
