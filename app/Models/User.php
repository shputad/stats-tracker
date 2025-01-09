<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'status'
    ];

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
        ];
    }

    /**
     * Relationship with roles.
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_user');
    }

    /**
     * Check if the user has a specific role.
     */
    public function hasRole($role)
    {
        return $this->roles()->where('name', $role)->exists();
    }

    /**
     * Assign a role to the user.
     */
    public function assignRole($role)
    {
        $roleInstance = is_string($role)
            ? Role::where('name', $role)->first()
            : $role;

        if ($roleInstance) {
            $this->roles()->attach($roleInstance);
        }
    }

    /**
     * Synchronize roles for the user.
     */
    public function syncRoles($roles)
    {
        $roleInstances = collect($roles)->map(function ($role) {
            return is_string($role)
                ? Role::where('name', $role)->first()
                : $role;
        })->filter();

        $this->roles()->sync($roleInstances->pluck('id')->toArray());
    }
}
