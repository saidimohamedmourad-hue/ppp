<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
       use HasFactory,HasUuids,SoftDeletes;
protected $table = "companies";

protected $keytype = "string";

public $incrementing = false;

    protected $fillable = [
        "name",
        "address",
        "industry",
        "website",
        "ownerId",
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
  public function owner(){
    return $this->belongsTo(User::class,'ownerId','id');
  }
    public function JobVacancies(){
        return $this->hasMany(JobVacancy::class,'companyId','id');
    }
    public function JobApplications(){
        return $this->hasManyThrough(JobApplication::class,JobVacancy::class,'companyId','jobVacancyId','id','id');
    }
    
}
