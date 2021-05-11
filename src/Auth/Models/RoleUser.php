<?php

namespace EgalFramework\Auth\Models;

use EgalFramework\Model\Deprecated\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Class RoleUser
 * @package App\PublicModels
 *
 * @property int $id
 * @property int $user_id
 * @property int $role_id
 * @property string $created_at
 * @property string $updated_at
 */
class RoleUser extends Model
{

    /** @var array */
    protected $guarded = ['id', 'created_at', 'updated_at'];

    /**
     * @return BelongsToMany
     */
    public function users()
    {
        return $this->belongsToMany('EgalFramework\\Auth\\Models\\User', 'users');
    }

    /**
     * @return BelongsToMany
     */
    public function roles()
    {
        return $this->belongsToMany('EgalFramework\\Auth\\Models\\Role', 'roles');
    }

}
