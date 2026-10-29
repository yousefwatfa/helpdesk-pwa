<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Category;
use App\Models\MainDepartment;
use App\Models\User;

class Department extends Model
{
  protected $fillable = ['name','main_department_id'];

    public function categories() {
        return $this->hasMany(Category::class);
    }

    public function mainDepartment() {
        return $this->belongsTo(MainDepartment::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class);
    }
}
