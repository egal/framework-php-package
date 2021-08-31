# Order by relation column

## Example

For order `products` by `name` column of `category` relation -
declare in `Product` model `orderByCategory` method,
implementing the necessary order:

```php
class Category extends Model
{

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'category_id', 'id');
    }

}

class Product extends Model
{

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function orderByCategory(Builder &$builder, string $column, string $direction)
    {
        $builder->join('categories', 'products.category_id', '=', 'categories.id', 'left', false);
        $builder->addSelect('products.*');
        $builder->addSelect('categories.' . $column . ' as category_' . $column);
        $builder->orderBy('category_' . $column, $direction);
    }

}
```
