<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SyllabusCdio extends Model
{
    protected $table = 'syllabus_cdios';

    protected $fillable = ['syllabus_id', 'code', 'description', 'position'];

    public $timestamps = true;
}
