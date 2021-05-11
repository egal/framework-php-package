<?php


namespace EgalFramework\Model\Tests\Samples\Models;

use EgalFramework\Model\Deprecated\Model;

/**
 * Class Country
 * @package App\PublicModels
 * @property int $id
 * @property string $name
 */
class Country extends Model
{

    /** @var array */
    protected $guarded = ['id', 'created_at', 'updated_at'];

}
