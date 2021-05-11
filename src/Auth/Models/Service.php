<?php

namespace EgalFramework\Auth\Models;

use EgalFramework\Auth\Container\API as MandateContainerAPI;
use EgalFramework\Auth\Container\Exception as MandateContainerException;
use EgalFramework\Common\AuthUserType;
use EgalFramework\Common\Interfaces\Kerberos\UserInterface;
use EgalFramework\Common\Session;
use EgalFramework\Common\Settings;
use EgalFramework\Kerberos\API;
use EgalFramework\Kerberos\ClientRequest;
use EgalFramework\Kerberos\Common;
use EgalFramework\Kerberos\Crypt;
use EgalFramework\Kerberos\Exceptions\IncorrectDataException;
use EgalFramework\Kerberos\Exceptions\TimeShiftException;
use EgalFramework\Model\Deprecated\Model;
use EgalFramework\Model\Deprecated\NotFoundException;
use Exception;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Cache;
use Psr\SimpleCache\InvalidArgumentException;

/**
 * Class User
 * @package App\PublicModels
 *
 * @property int $id
 * @property string $name
 * @property string $password
 * @property string $created_at
 * @property string $updated_at
 * @method where(string $string, string $email)
 * @method static find(int $id)
 */
class Service extends Model implements UserInterface
{

    /** @var array */
    protected $guarded = ['id', 'created_at', 'updated_at', 'type'];

    private API $kerberosApi;

    private MandateContainerAPI $mandateContainer;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->kerberosApi = new API;
        $this->mandateContainer = new MandateContainerAPI(Cache::store('memcached'), env('SESSION_TTL'));
    }

    /**
     * @return BelongsToMany
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany('EgalFramework\\Auth\\Models\\Role', 'role_service');
    }

    /**
     * @param array $data
     * @return array
     * @throws IncorrectDataException
     * @throws InvalidArgumentException
     * @throws TimeShiftException
     * @throws Exception
     * @roles @all
     */
    public function login($data)
    {
        if (!is_array($data)) {
            throw new IncorrectDataException('Data is not an array', 400);
        }
        $request = $this->kerberosApi->getClientRequestFromArray($data);
        /** @var Service $service */
        $service = $this->where('name', $request->getEmail())->first();
        if (!$service) {
            throw new Exception(sprintf('Service %s not found', $request->getEmail()), 401);
        }
        $service->password = (new Crypt)->decrypt($service->password, Settings::getAppKey());
        $this->kerberosApi->checkClientRequest($request, $service);
        $clientRequest = new ClientRequest($request->getEmail(), $request->getData());
        $this->kerberosApi->checkClientRequest($clientRequest, $service);
        $this->mandateContainer->putToken($service->name, json_encode($request->toArray(), JSON_UNESCAPED_SLASHES));
        $response = $this->kerberosApi->createKDCResponse($clientRequest, $service, Settings::getAppKey());
        return [
            Common::FIELD_SESSION_KEY => $request->toArray(),
            Common::FIELD_DATA => $response->getSessionKey()->getData(),
        ];
    }

    /**
     * @param array $data
     * @return bool
     * @roles @all
     * @throws InvalidArgumentException
     */
    public function checkLogged(array $data): bool
    {
        return $this->mandateContainer->hasToken(json_encode($data, JSON_UNESCAPED_SLASHES));
    }

    public function save(array $options = [])
    {
        $crypt = new Crypt;
        $this->password = $crypt->encrypt($this->password, Settings::getAppKey());
        return parent::save($options);
    }

    /**
     * User array of roles
     * @return array
     */
    public function getRolesArray(): array
    {
        $roles = [];
        /** @var Role $role */
        foreach ($this->roles()->getModels() as $role) {
            $roles[] = $role->internal_name;
        }
        return $roles;
    }

    /**
     * PHP 7.4 limitation
     * @return array
     */
    public function toArray(): array
    {
        return parent::toArray() + ['type' => $this->getType()];
    }

    public function getType(): int
    {
        return AuthUserType::SERVICE;
    }

    /**
     * @param string $data
     * @return string|null
     * @throws IncorrectDataException
     * @throws InvalidArgumentException
     * @throws NotFoundException
     * @throws MandateContainerException
     * @roles @service
     */
    public function getMandate(string $data): ?string
    {
        if (empty($data)) {
            return null;
        }
        $mandateArray = json_decode($data, true);
        if (json_last_error()) {
            throw new IncorrectDataException('Token can\'t be converted to an array: ' . json_last_error_msg(), 400);
        }
        $sender = Session::getMessage()->getSender();
        $request = $this->kerberosApi->getClientRequestFromArray($mandateArray);
        $mandate = $this->mandateContainer->getMandate($request->getEmail(), $data, $sender);
        if ($mandate) {
            return $mandate;
        }

        $user = $this->getUser($request);
        /** @var Service $service */
        $service = $this->where('name', $sender)->get()->first();
        if (!$service) {
            return null;
        }
        $service->password = (new Crypt)->decrypt($service->password, Settings::getAppKey());
        $clientRequest = new ClientRequest($request->getEmail(), $request->getData());
        $response = $this->kerberosApi->createKDCResponse($clientRequest, $user, $service->password);
        $mandate = $response->getMandate();
        $this->mandateContainer->putMandate($request->getEmail(), $data, $service->name, $mandate);
        return $mandate;
    }

    /**
     * @param ClientRequest $request
     * @return UserInterface
     * @throws IncorrectDataException
     * @throws NotFoundException
     */
    private function getUser(ClientRequest $request): UserInterface
    {
        $email = strtolower($request->getEmail());
        if (strpos($email, '@')) {
            /** @var User $user */
            $user = (new User)->where('email', $email)->first();
        } else {
            /** @var Service $user */
            $user = (new Service)->where('name', $email)->first();
            $user->password = (new Crypt)->decrypt($user->password, Settings::getAppKey());
        }
        if (!$user) {
            throw new NotFoundException('User not found', 401);
        }
        return $user;
    }

    /**
     * @return bool|null
     * @throws InvalidArgumentException
     * @throws Exception
     */
    public function delete()
    {
        $result = parent::delete();
        $this->mandateContainer->removeEmail($this->name);
        return $result;
    }

}
