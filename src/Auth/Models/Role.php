<?php

namespace EgalFramework\Auth\Models;

use EgalFramework\Model\Deprecated\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Class Role
 * @package App\PublicModels
 *
 * @property int $id
 * @property string $internal_name
 * @property string $name
 * @property bool $is_default
 * @property string $created_at
 * @property string $updated_at
 *
 * @method-roles create admin
 * @method-roles update admin
 * @method-roles delete admin
 * @method-roles getItem admin
 * @method-roles getItems admin
 * @method static where(string $string, bool $true)
 */
class Role extends Model
{

    /** @var array */
    protected $guarded = ['id', 'created_at', 'updated_at'];

}
