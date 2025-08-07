<?php
// File: app/Models/Department.php
// Department Model

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User; // If your User model is in App\Models

class Department extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
    ];


    // Get the admin who handles this department
// Get the first admin user assigned to this department
public function admin()
{
    return $this->hasOne(User::class, 'department_id')->where('role', 'admin');
}



// Get all programs under this department
public function programs()
{
    return $this->hasMany(Program::class);
}

}
