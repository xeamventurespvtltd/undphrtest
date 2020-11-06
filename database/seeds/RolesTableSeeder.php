<?php

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // $roles = [
        // 			'Main Administrator',
        // 			'HR',
        // 			'Management',
        // 			'DGM',
        // 			'GM',
        // 			'HOD',
        // 			'Dy.HOD',
        // 			'CMD',
        // 			'MD',
        // 			'Center Manager',
        // 			'Computer Clerk',
        // 			'Counsellor',
        // 			'Data Entry Operator',
        // 			'DEO',
        // 			'District Project Manager',
        // 			'Supervisor',
        //             'Machine Operator',
        //             'Guard'
        // 		 ];

        // foreach ($roles as $key => $value) {
		//  	Role::create(['name' => $value]);
		// }
		DB::statement("INSERT INTO `roles` (`name`, `guard_name`, `created_at`, `updated_at`) VALUES
		('Main Administrator', 'web', NULL, NULL),
		('NPO', 'web', NULL, NULL),
		('SPO', 'web', NULL, NULL),
		('PO', 'web', NULL, NULL),
		('VCCM', 'web', NULL, NULL)

		;");
    }
}
