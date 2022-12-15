<?php

namespace App\Http\Resources;

use App\User;
use Illuminate\Http\Resources\Json\JsonResource;

class messageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'message'=>$this['message'],
            'sender'=>new userResource(User::find($this['sender_id'])),
            'receiver'=>new userResource(User::find($this['receiver_id'])),
            'created_at'=>$this['created_at'],
            'group_id'=>$this['group_id']
        ];
    }
}

class userResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'=>$this->id,
            'name'=>$this->name,
            'email'=>$this->email,
        ];
    }
}