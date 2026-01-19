<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class JobVacancy extends Model
{
use HasFactory, HasUuids, SoftDeletes;
protected $table = "job_vacancies";

protected $keytype = "string";

public $incrementing = false;

    protected $fillable = [
        "title",
        "description",
        "location",
        "salary",
        'viewCount',
        "type",
        "jobCategoryId",
        "companyId",
        
    ];
       protected $dates = [
        "deleted_at"
    ];

    public function casts():array
    {
        return [   
                'delete_at' => 'datetime',
        ];
    }
 
    public function jobCategory()
    {
        return $this->belongsTo(JobCategory::class,'jobCategoryId','id');
    }
      public function company()
    {
        return $this->belongsTo(Company::class,'companyId','id');
    }

    public function jobApplications(){

        return $this->hasMany(JobApplication::class,'jobVacancyId','id');
    }
    
}
