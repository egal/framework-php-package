<?php

namespace EgalFramework\Model\Tests\Samples\Models;

use EgalFramework\Model\Deprecated\Model;

/**
 * Class Article
 * @package App\PublicModels
 * @property int $id
 * @property string $caption
 * @property string $content
 * @property int $country_id
 * @property bool $is_important
 * @property string $date_field
 * @property string $time_field
 * @property string $email
 * @property string $password
 * @property int $int_field
 * @property int $list_field
 * @property float $float_field
 * @property bool $bool_field
 * @property string $created_at
 * @property string updated_at
 */
class Test extends Model
{

    /** @var array */
    protected $guarded = ['id', 'created_at', 'updated_at'];

}
