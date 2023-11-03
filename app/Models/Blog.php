<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Blog extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'info',
    ];

    public function scopeOrderByCreatedAt($query)
    {
        return $query->orderBy('created_at', 'asc');
    }
}
