<?php

namespace Egal\Core\Auth;

use Egal\Core\Database\Metadata\Field as FieldMetadata;
use Egal\Core\Database\Metadata\Model as ModelMetadata;
use Egal\Core\Database\Model;
use Egal\Core\Facades\Gate;
use Firebase\JWT\JWT;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model as IlluminateModel;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class User extends IlluminateModel implements UserModelInterface, Authenticatable
{
    // TODO: Добавить sub, остальное перенести из auth-service v.2
    use \Illuminate\Auth\Authenticatable;

    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
      'email',
      'password'
    ];

    /**
     * @var string[]
     */
    private array $roles;

    protected static function boot()
    {
        parent::boot();
        static::creating(function (self $user) {
            Log::debug('here');
            $user->id = Str::uuid()->toString();
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

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function makeAccessToken(): string
    {
        return JWT::encode([
            'typ' => 'access',
            'exp' => Carbon::now()->addSeconds(24 * 60 * 60),
            'id' => $this->id,
            'roles' => $this->roles ?? [],
        ], config('auth.private_key'), 'RS256');
    }

    public function makeRefreshToken(): string
    {
        return JWT::encode([
            'typ' => 'refresh',
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
