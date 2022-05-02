<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InternshipFeatures extends Model
{
    use HasFactory, SoftDeletes;

    protected $hidden = [
        'deleted_at'
    ];

}
