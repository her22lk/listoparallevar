<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
class Purchase extends Model
{
    use HasFactory;

    protected $fillable = [
        'pack_id',
        'user_id',
        'seller_id',
        'calification_gived_id',
        'feedback_received_id',
        'payment_id',
        'code',
        'status',
        'amount',

    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function seller()
    {
        return $this->belongsTo(User::class);
    }
    public function pack()
    {
        return $this->belongsTo(Pack::class);
    }
    public function calificationGivenBy()
    {
        return $this->belongsTo(User::class, 'calification_given_id');
    }

    public function feedbackReceivedBy()
    {
        return $this->belongsTo(User::class, 'feedback_received_id');
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

}
