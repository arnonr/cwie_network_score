<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class University extends Model
{

    use HasFactory;
    protected $table = 'university';
    protected $primaryKey = 'id';

    protected $fillable = [
        'id',
        'name',
        'is_publish',
        'created_at',
        'created_by',
        'updated_at',
        'updated_by',
        'deleted_at',
    ];
}
