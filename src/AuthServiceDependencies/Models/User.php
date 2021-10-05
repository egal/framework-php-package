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
    use HasFactory;
    use HasRelationships;

    public static function actionLoginToService(string $token, string $serviceName): string
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
