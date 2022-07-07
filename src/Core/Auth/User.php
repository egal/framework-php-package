<?php

namespace Egal\Core\Auth;

use Egal\Core\Database\Metadata\Field as FieldMetadata;
use Egal\Core\Database\Metadata\Model as ModelMetadata;
use Egal\Core\Database\Model;
use Egal\Core\Facades\Gate;
use Firebase\JWT\JWT;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Concerns\HasRelationships;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model as IlluminateModel;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class User extends IlluminateModel implements UserModelInterface, Authenticatable
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
    /**
     * @var string[]
     */
    private array $roles;

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
                FieldMetadata::make('name')
                    ->required()
                    ->validationRules(['string'])
                    ->fillable()
            );
    }

    public function hasRole(string $name): bool
    {
        return in_array($name, $this->roles);
    }

    public function hasRoles(array $roles): bool
    {
        return count(array_intersect($this->roles, $roles)) == count($roles);
    }

    protected function getRoles(): array
    {
        return array_unique($this->roles->pluck('id')->toArray());
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_roles');
    }

    public function makeAccessToken(): string
    {
        return JWT::encode([
            'type' => 'access',
            'exp' => Carbon::now()->addSeconds(24 * 60 * 60),
            'sub' => $this->getAttribute($this->getKeyName()),
            'roles' => $this->roles ?? [],
        ], config('auth.private_key'), 'RS256');
    }

    public function makeRefreshToken(): string
    {
        return JWT::encode([
            'type' => 'refresh',
            'exp' => Carbon::now()->addSeconds(30 * 24 * 60 * 60),
        ], config('auth.private_key'), 'RS256');
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
