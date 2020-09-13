<?php

use App\Buttons;
use App\UserGroups;
use Carbon\Carbon;
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
        UserGroups::create([
                'name' => 'default',
                'description' => 'General Public',
            ]
        );

        $buttons = array(
            [
                'user_group' => 1,
                'evolution' => 1,
                'button_label' => 'Problem with me',
                'cause1' => 'Short Fused',
                'cause2' => 'Over Caring',
                'cause3' => 'Highly Concerned',
                'cause4' => 'Absent minded',
                'cause5' => 'Angry',
            ],
            [
                'user_group' => 1,
                'evolution' => 1,
                'button_label' => 'Problem with world',
                'cause1' => 'Dominating',
                'cause2' => 'Selfish',
                'cause3' => 'Bare society',
                'cause4' => 'Bad Education',
                'cause5' => 'Money Minded',
            ]
        );
        foreach($buttons as $button)
        {
            Buttons::create($button);
        }
    }
}
