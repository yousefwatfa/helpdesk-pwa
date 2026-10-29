<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use NotificationChannels\WebPush\HasPushSubscriptions;
use App\Models\Department;
use App\Models\Note;
use App\Models\Branch;


class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasPushSubscriptions;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'username',
        'fullname',
        'email',
        'password',
        'role_id',
        'is_root',
        'auth_type',
        'whatsapp_number',
        'office_location',
        'can_reopen_tickets',
        'can_access_finished_tickets',
    ];

    public function role() {
        return $this->belongsTo(Role::class);
    }

    public function tickets() {
        return $this->hasMany(Ticket::class, 'created_by');
    }

    public function assignedTickets() {
        return $this->belongsToMany(Ticket::class, 'ticket_user');
    }

    public function isRoot() {
        return $this->is_root;
    }

    public function departments()
    {
        return $this->belongsToMany(Department::class);
    }
    public function notes()
    {
        return $this->hasMany(Note::class);
    }

    public function branches()
    {
        return $this->belongsToMany(Branch::class);
    }

    public function receivesBroadcastNotificationsOn()
{
    return 'App.Models.User.' . $this->id;
}

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_root' => 'boolean',
            'can_reopen_tickets' => 'boolean',
            'can_access_finished_tickets' => 'boolean',
        ];
    }
}
