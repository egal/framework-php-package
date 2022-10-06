<?php

declare(strict_types=1);

namespace Egal\Auth\Tokens;

use Egal\Auth\Exceptions\InitializeUserServiceTokenException;
use Egal\Auth\Exceptions\UserServiceTokenException;
use Illuminate\Support\Carbon;

class UserServiceToken extends Token
{

    protected string $type = TokenType::USER_SERVICE;

    /**
     * @var mixed[]
     */
    protected array $authInformation;

    protected string $targetServiceName;

    /**
     * @throws \Egal\Auth\Exceptions\InitializeUserServiceTokenException
     */
    public static function fromArray(array $array): UserServiceToken
    {
        foreach (['type', 'auth_information', 'target_service_name'] as $index) {
            if (!array_key_exists($index, $array)) {
                throw new InitializeUserServiceTokenException('Incomplete information!');
            }
        }

        $token = new UserServiceToken();

        if ($array['type'] !== TokenType::USER_SERVICE) {
            throw new InitializeUserServiceTokenException('Type mismatch!');
        }

        // TODO: Разобраться зачем приведение типов.
        $token->setAuthInformation((array) $array['auth_information']);
        $token->aliveUntil = Carbon::parse($array['alive_until']);
        $token->targetServiceName = $array['target_service_name'];

        return $token;
    }

    public function getAuthInformation(): array
    {
        return $this->authInformation;
    }

    public function setAuthInformation(array $authInformation): void
    {
        $this->authInformation = $authInformation;
    }

    public function authInformationAboutRolesExists(): bool
    {
        return array_key_exists('roles', $this->authInformation);
    }

    public function authInformationAboutPermissionsExists(): bool
    {
        return array_key_exists('permissions', $this->authInformation);
    }

    /**
     * @throws \Egal\Auth\Exceptions\UserServiceTokenException
     */
    public function authInformationAboutRolesExistsOrFail(): bool
    {
        if (!$this->authInformationAboutRolesExists()) {
            throw new UserServiceTokenException('Missing information about roles!');
        }

        return true;
    }

    /**
     * @throws \Egal\Auth\Exceptions\UserServiceTokenException
     */
    public function authInformationAboutPermissionsExistsOrFail(): bool
    {
        if (!$this->authInformationAboutPermissionsExists()) {
            throw new UserServiceTokenException('Missing information about permissions!');
        }

        return true;
    }

    public function getRoles(): array
    {
        return $this->authInformationAboutRolesExists()
            ? $this->authInformation['roles']
            : [];
    }

    public function addRole(string $role): self
    {
        if (!isset($this->authInformation['roles'])) {
            $this->authInformation['roles'] = [];
        }

        $this->authInformation['roles'][] = $role;
        $this->authInformation['roles'] = array_unique($this->authInformation['roles']);

        return $this;
    }

    public function addPermission(string $permission): self
    {
        if (!isset($this->authInformation['permissions'])) {
            $this->authInformation['permissions'] = [];
        }

        $this->authInformation['permissions'][] = $permission;
        $this->authInformation['permissions'] = array_unique($this->authInformation['permissions']);

        return $this;
    }

    public function getUid(): string
    {
        return $this->getAuthInformation()['auth_identification'];
    }

    public function getPermissions(): array
    {
        return $this->authInformationAboutPermissionsExists()
            ? $this->authInformation['permissions']
            : [];
    }

    /**
     * @throws \Egal\Auth\Exceptions\UserServiceTokenException
     */
    public function toArray(): array
    {
        $this->authInformationAboutRolesExistsOrFail();
        $this->authInformationAboutPermissionsExistsOrFail();

        return [
            'type' => $this->type,
            'auth_information' => $this->authInformation,
            'alive_until' => $this->aliveUntil->toISOString(),
            'target_service_name' => $this->targetServiceName,
        ];
    }

    public function getTargetServiceName(): string
    {
        return $this->targetServiceName;
    }

    public function setTargetServiceName(string $targetServiceName): void
    {
        $this->targetServiceName = $targetServiceName;
    }

}
