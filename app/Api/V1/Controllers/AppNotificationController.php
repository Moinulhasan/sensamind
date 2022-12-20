<?php

namespace App\Api\V1\Controllers;

use App\Api\V1\Requests\DeviceNotificationTokenRequest;
use App\Api\V1\Requests\NotificationMessageRequest;
use App\Http\Controllers\Controller;
use App\UserDeviceTokens;
use Auth;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\JWTAuth;

class AppNotificationController extends Controller
{
    /**
     * Store Device Registration Token of user
     *
     * @param DeviceNotificationTokenRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateDeviceToken(DeviceNotificationTokenRequest $request, JWTAuth $JWTAuth)
    {
        $user = Auth::guard()->user();
        $params = $request->only('registration_id', 'device_id', 'type');
        $deviceToken = UserDeviceTokens::where('registration_id', '=', $params['registration_id'])->get();

        if ($deviceToken->count()) {
            return response()->json([
                'success' => true,
                'message' => "Registration id already exists"
            ]);
        }

        $newUserToken = new UserDeviceTokens();
        $newUserToken['user_id'] = $user->id;
        $newUserToken['registration_id'] = $params['registration_id'];
        $newUserToken['device_id'] = $params['device_id'];
        $newUserToken['type'] = $params['type'];

        if (!$newUserToken->save()) {
            return response()->json([
                'success' => false,
                'error' => array('message' => 'Couldn\'t save user token. Try again')
            ], 422);
        }
        return response()->json([
            'success' => true,
            'message' => 'User token registered successfully.'
        ], 201);

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
                try{
                    if ($value['error']) {
                        $errorTokenIndices[] = $key;
                    }
                }
                catch(\Exception $e){

                }
            }
            return $errorTokenIndices;
        }

        return true;
    }

    public function sendMessageToUser($details)
    {

        $url = 'https://fcm.googleapis.com/fcm/send';
        $fcmTokens = UserDeviceTokens::where('user_id', '=', $details->user_id)->pluck('registration_id')->all();
        if(count($fcmTokens) > 0){
            $serverKey = config('services.firebase.key');

            $data = [
                "registration_ids" => $fcmTokens,
                "notification" => [
                    "title" => $details->title,
                    "body" => $details->body,
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
                try{
                    if(count($failedTokens) > 0){
                        UserDeviceTokens::whereIn('registration_id', $failedTokens)->delete();
                    }
                }
                catch (\Exception $e){
                    Log::error($e);
                }
            }
            return true;
        }
        return false;
    }

    public function sendMessageToTopicSubscribers($details)
    {
        $url = 'https://fcm.googleapis.com/fcm/send';
        $serverKey = config('services.firebase.key');

        $data = [
            "topic" => $details->topic,
            "notification" => [
                "title" => $details->title,
                "body" => $details->body,
            ]
        ];
        $encodedData = json_encode($data);

        $headers = [
            'Authorization:key=' . $serverKey,
            'Content-Type: application/json',
        ];

        if ($this->_sendNotification($url, $headers, $encodedData)) {
            return true;
        }

        return false;
    }


    public function sendNotificationToUser(NotificationMessageRequest $request, JWTAuth $JWTAuth)
    {
        $user = Auth::guard()->user();

        if (!($user->role == 'admin' || $user->role == 'super_admin')) {
            return response()->json([
                'success' => false,
                'message' => 'Not enough privileges to perform operation.'
            ], 403);
        }

        $user_id = $request->user_id;
        $topic = $request->topic;

        if (!$user_id && !$topic) {
            return response()->json([
                'success' => false,
                'error' => array('message' => 'Required Parameter missing. user_id or topic is required')
            ], 422);
        }

        $success = false;

        if ($topic) {
            if ($this->sendMessageToTopicSubscribers($request)) {
                $success = true;
            }
        } else {
            if ($this->sendMessageToUser($request)) {
                $success = true;
            }
        }

        if ($success) {
            return response()->json([
                'success' => true,
                'message' => 'Message sent successfully.'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Error sending message to user.'
        ], 422);

    }
}
