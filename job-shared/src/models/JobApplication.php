<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class JobApplication extends Model
{
    use HasFactory ,  HasUuids,SoftDeletes;
    
    protected $table = "job_applications";

    protected $keytype = "string";

    public $incrementing = false;

    protected $fillable = [
        "status",
        "aiGeneratedScore",
        "aiGeneratedFeedback",
        "jobVacancyId",
        "userId",
        "resumeId",
        "cover_letter",
        "education_level",
    ];

      protected $dates = [
        "deleted_at"
    ];

    public function casts():array
    {
        return [
            'deleted_at' => 'datetime',
        ];
    }

    public function jobVacancy()
    {
        return $this->belongsTo(JobVacancy::class, 'jobVacancyId', 'id')->withTrashed();
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
