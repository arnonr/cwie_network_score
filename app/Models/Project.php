<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Project extends Model
{

    use HasFactory;
    protected $table = 'project';
    protected $primaryKey = 'id';

    protected $fillable = [
        'id',
        'code',
        'university_id',
        'project_file',
        'project_type_id',
        'status',
        'is_publish',
        'created_at',
        'created_by',
        'updated_at',
        'updated_by',
        'deleted_at',
    ];
}
