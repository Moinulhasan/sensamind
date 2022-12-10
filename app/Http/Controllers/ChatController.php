<?php

namespace App\Http\Controllers;

use App\ChatGroup;
use App\Http\Requests\ChatRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Kreait\Firebase\Database;

class ChatController extends Controller
{
    //
    /**
     * @var Database
     */
    private Database $database;
    /**
     * @var string
     */
    private string $table;
    /**
     * @var string
     */
    private string $groups;

    public function __construct(Database $database)
    {
        $this->database = $database;
        $this->table = "chats";
        $this->groups = 'groups';
    }

    public function index(Request $request)
    {
        $output = $this->database->getReference('0')
            ->push([
                'name' => 'My Application',
                'emails' => [
                    'support' => 'support@domain.tld',
                    'sales' => 'sales@domain.tld',
                ],
                'website' => 'https://app.domain.tld',
            ]);
        dd($output);
    }

    public function createMessage(ChatRequest $request)
    {
        $data = [
            'message' => $request->message,
            'receiver_id' => $request->receiver_id,
            'sender_id' => '1',
            'created_at' => Carbon::now(),
            'updated_at' => ''
        ];
        $list = $this->createGroup($request->all());
        $output = $this->database->getReference($this->table)
            ->push($data);

        if ($output) {
            return ['status' => true, 'message' => 'Message send successfully'];
        } else {
            return ['status' => false, 'message' => 'something went wrong !'];
        }
    }



    private function createGroup($data)
    {
       $check1 = ChatGroup::where('user_one',$data->sender_id)
                    ->where('user_two',$data->reciver_id)
                    ->first();
        if ($check1)
        {
            $output = $check1->id;
        }else{

        }
    }
}
