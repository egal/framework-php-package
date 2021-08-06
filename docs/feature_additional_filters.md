# Additional filters

## Example

You want realize filter like this:

```json
[ 'name', 'foo', 'bar']
```

Just create method in model:

```php

use Egal\Model\Builder;
use Egal\Model\Filter\FilterCondition;
use Egal\Model\Model;

class Bar extends Model
{
    
    public static function applyFooFilterCondition(Builder &$builder, FilterCondition $condition, string $beforeOperator): void
    {
        // EXAMPLE
        // $builder->where($condition->getField(), '=', $condition->getValue());
    }
    
}
```

Where method name consist from `apply` prefix, operator in pascal case
as body (`Foo`) and `FilterCondition` postfix,

In this method complete your builder.

> To reuse it in another Model, you can transfer this method to a Trait
> and connect Trait in the Model in which you need it.

