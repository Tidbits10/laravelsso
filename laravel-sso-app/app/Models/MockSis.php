<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MockSis extends Model
{
    protected $table = 'mock_sis';
    protected $primaryKey = 'student_number';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'student_number',
        'name',
        'email',
    ];
}
