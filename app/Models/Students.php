<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class Students extends Authenticatable
{
    use HasApiTokens, HasFactory;
    use SoftDeletes;

    protected $hidden = [
        'password',
        'remember_token',
        'deleted_at',

    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'graduate_year' => 'integer',
        'graduate_month' => 'integer',
    ];

    public function educationFacility()
    {
        return $this->belongsTo(EducationFacilities::class, 'education_facility_id');
    }

}
