<?php

namespace Egal\Core\Auth;

use Egal\Core\Facades\AuthManager;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model as BaseModel;

class Role extends BaseModel
{
    use HasFactory;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'name',
        'is_default'
    ];

    protected $guarder = [
        'created_at',
        'updated_at',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    protected static function boot()
    {
        parent::boot();
        static::created(function (Role $role) {
            if ($role->is_default) {
                AuthManager::newUser()->newCollection()->all()->each(function (UserModelInterface $user) use ($role) {
                    $user->roles()->attach($role->id);
                });
            }
        });
    }

}
