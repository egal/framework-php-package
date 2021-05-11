<?php

namespace App\PublicModels\Test;

use EgalFramework\Model\Model;

/**
 * Class TestModel
 * @package App\PublicModels\Test
 * @property int $id
 * @property string $created_at
 * @property string $updated_at
 * @property string $hash
 * @property array $json_field
 * @method-roles create admin
 * @method-roles update admin
 * @method-roles delete admin
 * @method-roles getItem admin
 * @method-roles getItems admin
 * @method-roles getTree admin
 */
class TestModel extends Model
{

    /** @var array */
    protected $guarded = ['id', 'created_at', 'updated_at'];

    protected $casts = [
        'json_field' => 'array',
    ];

}
