<?php

namespace App\Http\Controllers;

use App\Api\V1\Controllers\AppNotificationController;
use App\ChatGroup;
use App\Http\Requests\ChatRequest;
use App\Http\Resources\messageResource;
use App\UserDeviceTokens;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
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
        $group = $this->createGroup($request->all());
        $file = '';
        if ($request->hasFile('attachment')) {
            $fileTemp = $request->attachment->store('/attachment/' . $group->id,'public');
            $file = asset('/storage/' . $fileTemp);
        }
        $data = [
            'message' => $request->message,
            'receiver_id' => $request->receiver_id,
            'sender_id' => JWTAuth::user()->id,
            'group_id' => $group,
            'attachment' => $file,
            'created_at' => Carbon::now(),
            'updated_at' => ''
        ];
        $output = $this->database->getReference($this->table)
            ->push($data);

        if ($output) {
            $notification_formation = [
                'user_id' => $request->receiver_id,
                'title' => JWTAuth::user()->name . ' sent you a new message',
                'body' => $output->getValue()
            ];
            $this->notificationToUser($notification_formation);
            return ['status' => true, 'message' => 'Message send successfully'];
        } else {
            return ['status' => false, 'message' => 'something went wrong !'];
        }
    }


    public function getSingeUserMessage(Request $request, $id)
    {
        $output = $this->database->getReference($this->table)
            ->orderByChild('group_id')
            ->equalTo((int)$id)
            ->getSnapshot();
        return ['status' => true, 'data' => messageResource::collection($output->getValue())];
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

    public function notificationToUser($details)
    {

        $url = 'https://fcm.googleapis.com/fcm/send';
        $fcmTokens = UserDeviceTokens::where('user_id', '=', $details['user_id'])->pluck('registration_id')->all();
        if (count($fcmTokens) > 0) {
            $serverKey = config('services.firebase.key');

            $data = [
                "registration_ids" => $fcmTokens,
                "notification" => [
                    "title" => $details['title'],
                    "body" => $details['body'],
                ]
            ];
            $encodedData = json_encode($data);

            $headers = [
                'Authorization:key=' . $serverKey,
                'Content-Type: application/json',
            ];

            $response = $this->_sendNotification($url, $headers, $encodedData);

            if (is_array($response)) {
                $failedTokens = [];
                foreach ($response as $errorIdx) {
                    $failedTokens[] = $fcmTokens[$errorIdx];
                }
                try {
                    if (count($failedTokens) > 0) {
                        UserDeviceTokens::whereIn('registration_id', $failedTokens)->delete();
                    }
                } catch (\Exception $e) {
                    Log::error($e);
                }
            }
            return true;
        }
        return false;
    }
}
