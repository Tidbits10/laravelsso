<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceRequest extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'reason',
        'status',
        'qr_image',
        'qr_text',
        'remarks',
        'date_requested',
    ];

    protected $casts = [
        'date_requested' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
