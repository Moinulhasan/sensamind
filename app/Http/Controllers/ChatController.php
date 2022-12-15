<?php

namespace App\Http\Controllers;

use App\ChatGroup;
use App\Http\Requests\ChatRequest;
use App\Http\Resources\messageResource;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Kreait\Firebase\Database;
use Tymon\JWTAuth\Facades\JWTAuth;

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
            'sender_id' => JWTAuth::user()->id,
            'group_id' => $this->createGroup($request->all()),
            'created_at' => Carbon::now(),
            'updated_at' => ''
        ];
        $output = $this->database->getReference($this->table)
            ->push($data);

        if ($output) {
            return ['status' => true, 'message' => 'Message send successfully'];
        } else {
            return ['status' => false, 'message' => 'something went wrong !'];
        }
    }


    public function getSingeUserMessage(Request $request,$id)
    {
        $output = $this->database->getReference($this->table)
            ->orderByChild('group_id')
            ->equalTo((int)$id)
            ->getSnapshot();
      return ['status'=>true,'data'=>messageResource::collection($output->getValue())];
    }


    private function createGroup($data)
    {
        $user = JWTAuth::user()->id;
        $output = ChatGroup::where(function ($query) use ($data) {
            $query->where('user_one', $data['receiver_id'])
                ->orWhere('user_two', $data['receiver_id']);
        })->where(function ($query) use ($user) {
            $query->where('user_one', $user)
                ->orWhere('user_two', $user);
        })->first();

        if (!$output) {
            $result = ChatGroup::create([
                'user_one' => JWTAuth::user()->id,
                'user_two' => $data['receiver_id']
            ]);
            $output = $result->id;
        }
        return $output;
    }

    public function getUserChatList(Request $request)
    {
        $user = JWTAuth::user()->id;
        $output = ChatGroup::where(function ($query) use ($user) {
            $query->where('user_one', $user)
                ->orWhere('user_two', $user);
        })->get();
        $users = [];
        foreach ($output as $conversation) {
            if ($conversation->user_one === $user) {
                if ($conversation->receiver) {
                    $final = $conversation->receiver;
                    $final['chat_group_id'] = $conversation->id;
                    array_push($users, $final);
                }
            } else {
                if ($conversation->sender) {
                    $final_two = $conversation->sender;
                    $final_two['chat_group_id'] = $conversation->id;
                    array_push($users, $final_two);
                }
            }
        }
        if (count($users)) {
            return ['status' => true, 'data' => $users];
        } else {
            return ['status' => true, 'data' => array()];
        }
    }
}
