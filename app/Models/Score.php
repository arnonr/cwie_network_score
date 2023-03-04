<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Score extends Model
{

    use HasFactory;
    protected $table = 'score';
    protected $primaryKey = 'id';

    protected $fillable = [
        'id',
        'project_id',
        'user_id',
        'question_id',
        'answer',
        'status',
        'is_publish',
        'created_at',
        'created_by',
        'updated_at',
        'updated_by',
        'deleted_at',
    ];
}
