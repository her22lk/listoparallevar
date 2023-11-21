<?php

namespace App\Models;
use App\Models\User;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'payment_preference_id',
        'amount',
        'credit_card_number',
        'cvv',
        'expiration_date'
    ];

    public function user()
    {
       return $this->belongsTo(User::class, 'user_id');
    }
}
