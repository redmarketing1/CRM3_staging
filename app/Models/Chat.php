<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
    use HasFactory;

	protected $fillable = [
        'conversation_id',
        'type',
        'ai_model_id',
		'message',
		'status',
    ];

	public function ai_model()
    {
        return $this->hasOne(AiModel::class,'id','ai_model_id');
    }

	public function conversation()
    {
        return $this->hasOne(Conversation::class,'id','conversation_id');
    }

	public function attachments()
    {
        return $this->hasMany(ChatAttachment::class,'chat_id','id');
    }
}
