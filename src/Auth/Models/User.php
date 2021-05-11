<?php

namespace EgalFramework\Auth\Models;

use EgalFramework\Auth\Container\API as MandateContainerAPI;
use EgalFramework\Common\AuthUserType;
use EgalFramework\Common\Interfaces\Kerberos\UserInterface;
use EgalFramework\Common\Session;
use EgalFramework\Kerberos\API;
use EgalFramework\Kerberos\ClientRequest;
use EgalFramework\Kerberos\Common;
use EgalFramework\Kerberos\Exceptions\IncorrectDataException;
use EgalFramework\Kerberos\Exceptions\TimeShiftException;
use EgalFramework\Model\Deprecated\NotFoundException;
use EgalFramework\FilterQuery\FilterQuery;
use Exception;
use EgalFramework\Model\Deprecated\Model;
use EgalFramework\Model\Deprecated\ValidateException;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Cache;
use Psr\SimpleCache\InvalidArgumentException;

/**
 * Class User
 * @package App\PublicModels
 *
 * @property int $id
 * @property string $email
 * @property string $password
 * @property string $name
 * @property bool $is_confirmed
 * @property string $created_at
 * @property string $updated_at
 * @method where(string $string, string $email)
 */
class User extends Model implements UserInterface
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
        return $this->belongsToMany('EgalFramework\\Auth\\Models\\Role', 'role_user');
    }

    /**
     * @param $name
     * @param string $email
     * @param string $password
     * @return mixed
     * @throws ValidateException
     * @throws Exception
     * @roles @all
     */
    public function register($name, $email, $password)
    {
        $email = strtolower($email);
        if ($this->where('email', $email)->count() > 0) {
            throw new ValidateException('User is already exists', 418);
        }
        /** @var User $user */
        $user = $this->create([
            'name' => $name,
            'email' => $email,
            'password' => $password,
        ]);
        $confCode = bin2hex(random_bytes(16));
        app('redis')->set('user:confirmation:' . $confCode, $user->email);
        $roles = Role::where('is_default', true)->get();
        $user->roles()->saveMany($roles);
        //Mail::to([$user])->send(new ConfirmEmail($confCode));
        unset($user->password);
        return $user;
    }

    /**
     * @param array $data
     * @return array
     * @roles @all
     * @throws InvalidArgumentException
     * @throws IncorrectDataException
     */
    public function logout($data)
    {
        if (!is_array($data)) {
            throw new IncorrectDataException('Data is not an array', 400);
        }
        $request = $this->kerberosApi->getClientRequestFromArray($data);
        $this->mandateContainer->removeToken($request->getEmail(), Session::getMessage()->getMandate());
        return [];
    }

    /**
     * @return array
     * @throws ValidateException
     * @throws Exception
     * @roles @logged
     */
    public function emailConfirm()
    {
        $filter = new FilterQuery();
        $filter->setQuery(Session::getMessage()->getQuery());
        $confCode = $filter->getField('confirmationCode');
        $email = app('redis')->get('user:confirmation:' . $confCode);
        if ($this->email == $email) {
            $this->update(['is_confirmed' => 1]);
        }
        return [];
    }

    /**
     * @param string $email
     * @param string $newEmail
     * @return array
     * @throws InvalidArgumentException
     * @throws ValidateException
     * @roles @logged
     */
    public function changeEmail($email, $newEmail)
    {
        $email = strtolower($email);
        $newEmail = strtolower($newEmail);
        $validateCallback = Session::getValidateCallback();
        $errors = $validateCallback(['email' => $email, 'new_email' => $newEmail], [
            'email' => 'email|required|exists:users,email',
            'new_email' => 'email|required|unique:users,email',
        ]);
        if ($errors) {
            throw new ValidateException('Email address is not correct', 400);
        }
        $user = $this->where('email', $email)->first();
        $user->email = $newEmail;
        $user->save();
        $this->mandateContainer->removeEmail($user->email);
        Session::getModelManager()->flushCache($this->className, $user->id);
        unset($user->password);
        return $user;
    }

    /**
     * @param string $email
     * @param string $password
     * @return array
     * @throws ValidateException
     * @roles @logged
     */
    public function changePassword($email, $password)
    {
        $email = strtolower($email);
        $validateCallback = Session::getValidateCallback();
        $errors = $validateCallback(['email' => $email, 'password' => $password], [
            'email' => 'required|email|exists:users',
            'password' => 'required',
        ]);
        if ($errors) {
            throw new ValidateException('Email address is not correct', 400);
        }
        $user = $this->where('email', $email)->first();
        $user->password = $password;
        $user->save();
        Session::getModelManager()->flushCache($this->className, $user->id);
        unset($user->password);
        return $user;
    }

    /**
     * @param array $data
     * @return array
     * @throws IncorrectDataException
     * @throws InvalidArgumentException
     * @throws NotFoundException
     * @throws TimeShiftException
     * @throws ValidateException
     * @roles @all
     */
    public function login($data)
    {
        if (!is_array($data)) {
            throw new IncorrectDataException('Data is not an array', 400);
        }
        $request = $this->kerberosApi->getClientRequestFromArray($data);
        $user = $this->validateAndGetUser($request);
        $clientRequest = new ClientRequest($user->email, $request->getData());
        $this->kerberosApi->checkClientRequest($clientRequest, $user);
        $this->mandateContainer->putToken($user->email, json_encode($request->toArray(), JSON_UNESCAPED_SLASHES));
        $response = $this->kerberosApi->createKDCResponse($clientRequest, $user, env('APP_KEY'));
        return [
            Common::FIELD_SESSION_KEY => $request->toArray(),
            Common::FIELD_DATA => $response->getSessionKey()->getData(),
        ];
    }

    /**
     * @param ClientRequest $request
     * @return User
     * @throws IncorrectDataException
     * @throws NotFoundException
     * @throws TimeShiftException
     * @throws ValidateException
     */
    private function validateAndGetUser(ClientRequest $request): User
    {
        // validation
        $email = strtolower($request->getEmail());
        $validateCallback = Session::getValidateCallback();
        $errors = $validateCallback([
            Common::FIELD_EMAIL => $email,
            Common::FIELD_DATA => $request->getData(),
        ], [
            Common::FIELD_EMAIL => 'required|email|exists:users',
            Common::FIELD_DATA => 'required',
        ]);
        if ($errors) {
            throw new ValidateException('Email address is not correct or data field is not set', 400);
        }
        // get user
        $user = $this->where('email', $email)->first();
        if (!$user) {
            throw new NotFoundException('User not found', 401);
        }
        $this->kerberosApi->checkClientRequest($request, $user);
        return $user;
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
     * @return array
     */
    public function toArray(): array
    {
        return parent::toArray() + ['type' => $this->getType()];
    }

    public function getType(): int
    {
        return AuthUserType::USER;
    }

    /**
     * @return bool|null
     * @throws InvalidArgumentException
     * @throws Exception
     */
    public function delete()
    {
        $result = parent::delete();
        $this->mandateContainer->removeEmail($this->email);
        return $result;
    }

}
