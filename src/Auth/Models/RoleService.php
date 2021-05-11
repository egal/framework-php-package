<?php

namespace EgalFramework\Auth\Models;

use EgalFramework\Model\Deprecated\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Class RoleUser
 * @package App\PublicModels
 *
 * @property int $id
 * @property int $service_id
 * @property int $role_id
 * @property string $created_at
 * @property string $updated_at
 */
class RoleService extends Model
{

    /** @var array */
    protected $guarded = ['id', 'created_at', 'updated_at'];

    /**
     * @return BelongsToMany
     */
    public function services(): BelongsToMany
    {
        return $this->belongsToMany('EgalFramework\\Auth\\Models\\Service', 'services');
    }

    /**
     * @return BelongsToMany
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany('EgalFramework\\Auth\\Models\\Role', 'roles');
    }

}
