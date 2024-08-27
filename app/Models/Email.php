<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Email extends Model
{
    use HasFactory;
	protected $fillable = [
        'sender_id',
        'recipient_id',
        'subject',
        'message',
        'project_id',
        'type',
        'type_id',
        'cc_emails',
        'status',
        'estimations',
        'attachments',
    ];

    public static $status = [
        'Un-Send', 'Sent'
    ];

    // Define the polymorphic relationship for sender and recipient
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function recipient()
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }
}
