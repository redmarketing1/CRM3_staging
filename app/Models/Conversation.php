<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Taskly\Entities\Project;

class Conversation extends Model
{
    use HasFactory;

	protected $fillable = [
		'name',
        'project_id',
        'user_id',
        'workspace'
    ];

	protected $appends = [
        'last_chat_msg',
		'last_chat_model',
	];

	public function getLastChatMsgAttribute() {
		$last_chat_msg = '';
		$last_msg = Chat::where('conversation_id', $this->id)->orderBy('id', 'DESC')->first();
		if(isset($last_msg->id)) {
			$last_chat_msg = $last_msg;
		}
		return $last_chat_msg;
	}

	public function getLastChatModelAttribute() {
		$last_chat_model = '';
		$last_msg = Chat::where('conversation_id', $this->id)->orderBy('id', 'DESC')->first();
		if(isset($last_msg->id)) {
			$last_chat_model = $last_msg->ai_model_id;
		}
		return $last_chat_model;
	}

	public function project()
    {
        return $this->hasOne(Project::class,'id','project_id');
    }

	public function user()
    {
        return $this->hasOne(User::class,'id','user_id');
    }

	public function ai_model()
    {
        return $this->hasOne(AiModel::class,'id','ai_model_id');
    }

	public function chats()
    {
        return $this->hasMany(Chat::class,'conversation_id','id');
    }

}
