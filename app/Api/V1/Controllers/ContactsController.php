<?php

namespace App\Api\V1\Controllers;

use App\Api\V1\Requests\ContactRequest;
use App\Api\V1\Requests\SubscriptionRequest;
use App\Models\ContactFormSubmission;
use App\Http\Controllers\Controller;
use App\Mail\NewContact;
use App\Mail\SubscriptionSuccess;
use App\Mail\VerifyEmail;
use App\Models\MailingList;
use App\Models\UserVerification;
use Carbon\Carbon;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tymon\JWTAuth\JWTAuth;

class ContactsController extends Controller
{
    public function subscribe(SubscriptionRequest $request)
    {

        $token = Hash::make($request->email);
        
        $subToken = $token.Str::random(40);
        $params = ['email' => $request->email, 'subscription_token' => $subToken];
        if(MailingList::where('email',$request->email)->first()){
            return response()->json([
                'success' => true,
                'message' => 'You have already subscribed to our mailing list'
            ]);
        }
        $mailList = new MailingList($params);
        if($mailList->save())
        {
            $this->sendSubscriptionMail($request->email,$subToken);
            return response()->json([
                'success' => true,
                'message' => 'You are successfully added to our mailing list'
            ]);
        }
    }

    public function contactDetails(ContactRequest $request)
    {
        $params = $request->only(['name','email','subject','message']);
        $contact = new ContactFormSubmission($params);
        if($contact->save()){
            $this->sendContactDetailToAdmin($params);
            return response()->json([
                'success' => true,
                'message' => 'Your contact request is successfully submitted.'
            ]);
        }
    }

    public function unSubscribe(SubscriptionRequest $request)
    {
        $token = $request->token;
        if($token){
            $subscriber = MailingList::Where('subscription_token',$token);
            if($subscriber){
                if($subscriber->delete()){
                    return response()->json([
                        'success' => true,
                        'message' => 'Successfully unsubscribed from mailing list'
                    ],200);
                }
                else {
                    return response()->json([
                        'success' => false,
                        'message' => 'Error unsubscribing user. Contact admin to unsubscribe.'
                    ],422);
                }
            }
            return response()->json([
                'success' => true,
                'message' => 'Already, excluded from our mailing lists.'
            ],200);
        }
        return response()->json([
            'success' => false,
            'message' => 'Token invalid. Kindly use your unsubscription link sent to your mail id.'
        ],422);
    }

    public function sendSubscriptionMail($email,$token)
    {
        $baseUrl = Config::get('app.base_url');
        $actionUrl = $baseUrl.'/mailing_list/unsubscribe/'.$token;
        $details = ['actionUrl' => $actionUrl];
        Mail::to($email)->send(new SubscriptionSuccess($details));
    }

    public function sendContactDetailToAdmin($details)
    {
        $admin = Config::get('app.admin_mail');
        Mail::to($admin)->send(new NewContact($details));
    }
}
