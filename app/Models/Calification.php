<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Calification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'stars',
        'comment',
        'tags',
        'name'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}