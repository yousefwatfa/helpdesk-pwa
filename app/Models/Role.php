<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $fillable = ['name'];

    public function users() {
        return $this->hasMany(User::class);
    }

    /** Arabic name for the UI; `name` stays the English key the code checks. */
    public function getLabelAttribute(): string
    {
        return match ($this->name) {
            'Admin' => 'دعم فني (أدمن)',
            'Member' => 'موظف',
            'Viewer' => 'مشاهد',
            default => $this->name,
        };
    }
}
