<?php

// -------------------------------------------------------------------------------
// * File: app/Models/User.php
// * Description: User model for all roles (admin, faculty, student) in Syllaverse
// -------------------------------------------------------------------------------
// 📜 Log:
// [2025-08-08] Added 'designation' and 'employee_code' to $fillable so Admin profile saves HR fields.
// -------------------------------------------------------------------------------

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * This lets us safely mass-assign HR fields from the profile form.
     * (Without this, Laravel would ignore those inputs.)
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'google_id',
        'role',
        'status',
        'designation',     // ✅ Added
        'employee_code',   // ✅ Added
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * Keep sensitive auth fields out of arrays/JSON.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Casts for specific attributes.
     *
     * This ensures email verification is a DateTime and the password is hashed.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // ░░░ START: Relationships for manage-accounts ░░░

/** All chair-role requests this user has submitted. */
public function chairRequests()
{
    return $this->hasMany(\App\Models\ChairRequest::class);
}

/** All chair appointments (authority) granted to this user. */
public function appointments()
{
    return $this->hasMany(\App\Models\Appointment::class);
}

// ░░░ END: Relationships ░░░

}
