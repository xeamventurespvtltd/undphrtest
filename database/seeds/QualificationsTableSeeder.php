<?php

use Illuminate\Database\Seeder;
use App\Qualification;

class QualificationsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
    	DB::statement("INSERT INTO `qualifications` (`id`, `name`) VALUES
		(1, 'HIGH SCHOOL'),
		(2, 'BCA'),
		(3, 'B.COM'),
		(4, 'M.COM'),
		(5, 'B.SC(IT)'),
		(6, 'M.SC(IT)'),
		(7, 'B.SC(COMPUTER SCIENCE)'),
		(8, 'M.SC(COMPUTER SCIENCE)'),
		(9, 'BA'),
		(10, 'BBA'),
		(11, 'MA'),
		(12, 'PHD'),
		(13, 'MBA'),
		(14, 'B.Tech'),
		(15, 'MCA'),
		(16, 'B.SC'),
		(17, 'MCP'),
		(18, 'MBA - HR & Finance'),
		(19, 'MBA - HR'),
		(20, 'MBA - HR & Marketing'),
		(21, 'PGDMA'),
		(22, 'Diploma'),
		(23, 'CMA-INTER'),
		(24, 'MSW'),
		(25, 'PGDCA'),
		(26, 'Master in cosmolodgy'),
		(27, 'Graduation'),
		(28, 'Post Graduation');");
  
    }
}
