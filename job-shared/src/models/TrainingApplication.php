<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TrainingApplication extends Model
{
    use HasFactory, HasUuids, SoftDeletes;
    
    protected $table = "training_applications";

    protected $keyType = "string";

    public $incrementing = false;

    protected $fillable = [
        "status",
        "is_waitlist",
        "aiGeneratedScore",
        "aiGeneratedFeedback",
        "trainingSessionId",
        "userId",
        "resumeId",
        "cover_letter",
    ];

    protected $casts = [
        "is_waitlist" => "boolean",
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

    public function trainingSession()
    {
        return $this->belongsTo(TrainingSession::class, 'trainingSessionId', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'userId', 'id');
    }

    public function resume()
    {
        return $this->belongsTo(Resume::class, 'resumeId', 'id');
    }
}
