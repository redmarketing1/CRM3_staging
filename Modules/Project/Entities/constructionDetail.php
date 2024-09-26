<?php

namespace Modules\Project\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class constructionDetail extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'company_name',
        'title',
        'salutation',
        'first_name',
        'last_name',
        'email',
        'mobile',
        'phone',
        'website',
        'address_1',
        'address_2',
        'city',
        'state',
        'country',
        'zipcode',
        'lat',
        'long',
        'tax_number',
        'notes',
        'created_by',
    ];

    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id');
    }
}
