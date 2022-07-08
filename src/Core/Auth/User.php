<?php

namespace Egal\Core\Auth;

use Egal\Core\Database\Metadata\Field as FieldMetadata;
use Egal\Core\Database\Metadata\Model as ModelMetadata;
use Egal\Core\Database\Model;
use Egal\Core\Facades\Gate;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Concerns\HasRelationships;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class User extends Model implements UserModelInterface, Authenticatable
{
    use \Illuminate\Auth\Authenticatable;
    use HasFactory;
    use HasRelationships;


    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $hidden = [
        'password',
    ];

    protected $guarder = [
        'created_at',
        'updated_at',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function (self $user) {
            $user->setAttribute($user->getKeyName(), (string) Str::uuid());
        });
        static::created(function (User $user) {
            $defaultRoles = Role::query()
                ->where('is_default', true)
                ->get();
            $user->roles()
                ->attach($defaultRoles->pluck('id'));
        });
    }

    public function initializeMetadata(): ModelMetadata
    {
        return ModelMetadata::make(static::class)
            ->fields(
                FieldMetadata::make('email')
                    ->required()
                    ->validationRules(['string','email','max:255','unique:users']),
                FieldMetadata::make('password')
                    ->required()
                    ->validationRules(['string','min:6'])
            );
    }

    public function hasRole(string $name): bool
    {
        return in_array($name, $this->roles->pluck('name')->toArray());
    }

    public function hasRoles(array $roles): bool
    {
        return count(array_intersect($this->roles->pluck('name')->toArray(), $roles)) == count($roles);
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_roles');
    }

    public function can(Ability $ability, Model|string $model): bool
    {
        return Gate::check($this, $ability, $model);
    }

    public function findById(string $id)
    {
        return self::query()->find($id);
    }

}
