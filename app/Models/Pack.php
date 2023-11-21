<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pack extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'price',
        'description',
        'time_start',
        'time_end',
        'user_id',
        'tags',
        'photo_url',
        'stock'
    ];

///relacion de pack a user    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function Seller()
    {
        return $this->belongsTo(User::class);
    }
}