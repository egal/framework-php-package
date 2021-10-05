<?php

namespace Egal\AuthServiceDependencies\Models;

use Egal\Auth\Tokens\UserMasterToken;
use Egal\Auth\Tokens\UserServiceToken;
use Egal\AuthServiceDependencies\Exceptions\EmptyPasswordException;
use Egal\AuthServiceDependencies\Exceptions\LoginException;
use Egal\AuthServiceDependencies\Exceptions\PasswordHashException;
use Egal\AuthServiceDependencies\Exceptions\UserNotIdentifiedException;
use Egal\Model\Model;
use Egal\Model\Traits\UsesUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Staudenmeir\EloquentHasManyDeep\HasRelationships;

abstract class User extends Model
{

    use UsesUuid;
    use HasFactory;
    use HasRelationships;

    public static function actionRegisterByEmailAndPassword(string $email, string $password): self
    {
        if (!$password) {
            throw new EmptyPasswordException();
        }

        $user = new static();
        $user->setAttribute('email', $email);
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        if (!$hashedPassword) {
            throw new PasswordHashException();
        }

        $user->setAttribute('password', $hashedPassword);
        $user->save();

        return $user;
    }

    public static function actionLoginByEmailAndPassword(string $email, string $password): string
    {
        /** @var User $user */
        $user = self::query()
            ->where('email', '=', $email)
            ->first();

        if (!$user || !password_verify($password, $user->getAttribute('password'))) {
            throw new LoginException('Incorrect Email or password!');
        }

        $umt = new UserMasterToken();
        $umt->setSigningKey(config('app.service_key'));
        $umt->setAuthIdentification($user->getAuthIdentifier());

        return $umt->generateJWT();
    }

    final public static function actionLoginToService(string $token, string $serviceName): string
    {
        /** @var UserMasterToken $umt */
        $umt = UserMasterToken::fromJWT($token, config('app.service_key'));
        $umt->isAliveOrFail();

        /** @var User $user */
        $user = static::query()->find($umt->getAuthIdentification());
        $service = Service::find($serviceName);
        if (!$user) {
            throw new UserNotIdentifiedException();
        }
        if (!$service) {
            throw new LoginException('Service not found!');
        }

        $ust = new UserServiceToken();
        $ust->setSigningKey($service->getKey());
        $ust->setAuthInformation($user->generateAuthInformation());

        return $ust->generateJWT();
    }

    abstract protected function getRoles(): array;

    abstract protected function getPermissions(): array;

    protected function generateAuthInformation(): array
    {
        return array_merge(
            $this->fresh()->toArray(),
            [
                'auth_identification' => $this->getAuthIdentifier(),
                'roles' => $this->getRoles(),
                'permissions' =>  $this->getPermissions(),
            ]
        );
    }

    /**
     * Get the name of the unique identifier for the user.
     */
    public function getAuthIdentifierName(): string
    {
        return $this->getKeyName();
    }

    /**
     * Get the unique identifier for the user.
     *
     * @return mixed
     */
    public function getAuthIdentifier()
    {
        return $this->{$this->getAuthIdentifierName()};
    }

}
