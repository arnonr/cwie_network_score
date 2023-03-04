<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Question extends Model
{

    use HasFactory;
    protected $table = 'question';
    protected $primaryKey = 'id';

    protected $fillable = [
        'id',
        'name',
        'level',
        'project_type_id',
        'detail',
        'total_score',
        'is_check',
        'is_publish',
        'created_at',
        'created_by',
        'updated_at',
        'updated_by',
        'deleted_at',
    ];
}
