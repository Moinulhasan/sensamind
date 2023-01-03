<?php

namespace App\Http\Controllers;

use App\Api\V1\Controllers\AppNotificationController;
use App\ChatGroup;
use App\Http\Requests\ChatRequest;
use App\Http\Resources\messageResource;
use App\User;
use App\UserDeviceTokens;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Database;
use mysql_xdevapi\Exception;
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
        $type = '';
        $file_name = '';
        $supported = ['jpeg', 'gif', 'png', 'apng', 'svg', 'bmp', 'jpg'];
        if ($request->hasFile('attachment')) {
            $fileTemp = $request->attachment->store('/attachment/' . $group->id, 'public');
            $extentation = $request->attachment->getClientOriginalExtension();

            if (in_array($extentation, $supported)) {
                $type = 'image';
            } else {
                $type = 'file';
            }
            $file_name = $request->attachment->getClientOriginalName();
            $file = asset('/storage/' . $fileTemp);


        }
        $data = [
            'message' => $request->message,
            'receiver_id' => $request->receiver_id,
            'sender_id' => JWTAuth::user()->id,
            'group_id' => $group->id,
            'attachment' => $file,
            'origin_name' => $file_name,
            'type' => $type,
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
            return ['status' => true, 'message' => 'Message send successfully', 'data' => $output->getValue()];
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
        try {
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
                $output = $result;
            }
            $this->updateLiveTime($output);
            return $output;
        } catch (\Exception $exception) {
            return false;
        }
    }

    private function updateLiveTime($data)
    {
        $check = ChatGroup::find($data->id);
        $check->recent_active = Carbon::now();
        $check->save();
        return true;
    }

    public function getUserChatList(Request $request)
    {
        $user = JWTAuth::user()->id;
        $output = ChatGroup::where(function ($query) use ($user) {
            $query->where('user_one', $user)
                ->orWhere('user_two', $user);
        })->orderBy('recent_active', 'desc')->get();
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

    public function searchUser(Request $request)
    {
        $login = JWTAuth::user();
        if ($login->role == 'super_admin') {
            $user = User::where('email', 'like', '%' . $request->name . '%')
                ->orWhere('name', 'like', '%' . $request->name . '%')
                ->get();
        } else {
            $user = User::where('role', '=', 'super_admin')
                ->where('name', 'like', '%' . $request->name . '%')
                ->get();
        }
        if (count($user)) {
            return ['status' => true, 'data' => $user];
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

    private function _sendNotification($url, $headers, $encodedData)
    {

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $encodedData);


        $result = curl_exec($ch);

        if ($result === FALSE) {
            Log::error("Curl failed");
            die('Curl failed: ' . curl_error($ch));
        }

        $resultData = json_decode($result, true);
        curl_close($ch);

        if ($resultData['failure'] > 0) {
            Log::error($resultData);
            $resultsByToken = $resultData['results'];
            $errorTokenIndices = [];
            foreach ($resultsByToken as $key => $value) {
                try {
                    if ($value['error']) {
                        $errorTokenIndices[] = $key;
                    }
                } catch (\Exception $e) {

                }
            }
            return $errorTokenIndices;
        }

        return true;
    }
}
