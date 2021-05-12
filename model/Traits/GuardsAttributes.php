<?php /** @noinspection PhpUnused */

namespace Egal\Model\Traits;

trait GuardsAttributes
{

    /**
     * @var string[]
     */
    protected $guarded = [
        'created_at',
        'updated_at',
    ];

    /**
     * @var string[]
     */
    protected $fillable = [
        'id',
        'name',
        'is_default',
    ];

}
