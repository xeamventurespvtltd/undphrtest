<?php

use Illuminate\Database\Seeder;
use App\Skill;

class SkillsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement("INSERT INTO `skills` (`id`, `name`) VALUES
        (1, 'C'),
        (2, 'Php'),
        (3, 'Java'),
        (4, 'Python'),
        (5, 'Ruby'),
        (6, 'Node js'),
        (7, 'C++'),
        (8, 'C#'),
        (9, 'F#'),
        (10, 'Management'),
        (11, 'Clerical'),
        (12, 'Tally'),
        (13, 'Organizational skills'),
        (14, 'Office Management'),
        (15, 'Decision-making'),
        (16, 'Manual Testing'),
        (17, 'Selenium'),
        (18, 'Node.js'),
        (19, 'Jmeter'),
        (20, 'HTML'),
        (21, 'CSS'),
        (22, 'Jquery'),
        (23, 'Java Script'),
        (24, 'Project Management'),
        (25, 'Digital Marketing'),
        (26, 'Graphic Designer'),
        (27, 'Web designer'),
        (28, 'System Management'),
        (29, 'Network Management'),
        (30, 'SEO'),
        (31, 'S.M.O'),
        (32, 'Website Management'),
        (33, 'Design Management'),
        (34, 'Computer Hardware Maintenance'),
        (35, 'LAN'),
        (36, 'Troubleshooting'),
        (37, 'Business Development'),
        (38, 'Client Acquisition'),
        (39, 'Recruitment'),
        (40, 'Compliance'),
        (41, 'HR - Generalist'),
        (42, 'Emp ID Genrartion'),
        (43, 'To Genrarte PF/ESI'),
        (44, 'Account Management'),
        (45, 'Brand Management'),
        (46, 'Strategic Marketing'),
        (47, 'Contract Negotiation'),
        (48, 'Integrated Marketing'),
        (49, 'Staff Management'),
        (50, 'Sales Planning & Analysis'),
        (51, 'Lead Generation'),
        (52, 'Google Analytics'),
        (53, 'Data Analysis'),
        (54, 'Outbound Marketing'),
        (55, 'Social Media Advertising'),
        (56, 'Finance'),
        (57, 'Accounts'),
        (58, 'Trainer'),
        (59, 'Counsellor'),
        (60, 'Hair Trainer'),
        (61, 'Makeup Artist');");
    }
}
