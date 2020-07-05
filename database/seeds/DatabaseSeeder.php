<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('labels')->insert([
            'button_label' => 'Problem with me',
            'cause1' => 'Short Fused',
            'cause2' => 'Over Caring',
            'cause3' => 'Highly Concerned',
            'cause4' => 'Absent minded',
            'cause5' => 'Angry',
        ]);
        DB::table('labels')->insert([
            'button_label' => 'Problem with world',
            'cause1' => 'Dominating',
            'cause2' => 'Selfish',
            'cause3' => 'Bare society',
            'cause4' => 'Bad Education',
            'cause5' => 'Money Minded',
        ]);
        DB::table('evolutions')->insert([
            'title' => 'Evolution 1',
            'description' => 'Evolution 1 start phase',
            'button_1' => 1,
            'button_2' => 2
        ]);
    }
}
