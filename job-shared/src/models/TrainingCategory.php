<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TrainingCategory extends Model
{
    use HasFactory, HasUuids, SoftDeletes;
    
    protected $table = "training_categories";
    protected $keyType = "string";

    public $incrementing = false;

    protected $fillable = [
        "name",
        "description",
    ];
    protected $dates = [
        "deleted_at"
    ];

    public function casts(): array
    {
        return [
            'deleted_at' => 'datetime',
        ];
    }

    public function trainingSessions()
    {
        return $this->hasMany(TrainingSession::class, 'trainingCategoryId', 'id');
    }
}
