<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TrainingSession extends Model
{
    use HasFactory, HasUuids, SoftDeletes;
    protected $table = "training_sessions";

    protected $keyType = "string";

    public $incrementing = false;

    protected $fillable = [
        "title",
        "trainingDate",
        "endDate",
        "startTime",
        "endTime",
        "maxParticipants",
        "currentParticipants",
        "status",
        "description",
        "location",
        "salary",
        'viewCount',
        "trainingCategoryId",
        "schoolId",
        
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
 
    public function trainingCategory()
    {
        return $this->belongsTo(TrainingCategory::class,'trainingCategoryId','id');
    }
      public function school()
    {
        return $this->belongsTo(School::class,'schoolId','id');
    }

    public function trainingApplications()
    {
        return $this->hasMany(TrainingApplication::class, 'trainingSessionId', 'id');
    }
    
}
