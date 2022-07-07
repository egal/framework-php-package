<?php

namespace Egal\Core\Auth;

use Illuminate\Database\Eloquent\Model as BaseModel;

class UserRole extends BaseModel
{
    protected $fillable = [
        'user_id',
        'role_id',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];
}
