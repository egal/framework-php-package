<?php

namespace EgalFramework\Auth\Container;

use EgalFramework\Common\Interfaces\MandateContainerInterface;
use Illuminate\Contracts\Cache\Repository;
use Psr\SimpleCache\InvalidArgumentException;

class API implements MandateContainerInterface
{

    const PREFIX_TOKEN = 'Token_';
    const PREFIX_MANDATE = 'Mandate_';

    private Repository $cacheService;

    private int $ttl;

    public function __construct(Repository $cacheService, int $ttl)
    {
        $this->cacheService = $cacheService;
        $this->ttl = $ttl;
    }

    /**
     * @param string $email
     * @param string $token
     * @throws InvalidArgumentException
     */
    public function putToken(string $email, string $token): void
    {
        $tokens = $this->cacheService->get($email, []);
        if (!isset($tokens[$token])) {
            $tokens[$token] = true;
        }
        $this->cacheService->set($email, $tokens, $this->ttl);
        $this->cacheService->set(self::PREFIX_TOKEN . $token, [], $this->ttl);
    }

    /**
     * @param string $token
     * @return bool
     * @throws InvalidArgumentException
     */
    public function hasToken(string $token): bool
    {
        $result = $this->cacheService->get(self::PREFIX_TOKEN . $token);
        if (!is_null($result)) {
            $this->cacheService->set(self::PREFIX_TOKEN . $token, [], $this->ttl);
        }
        return (bool)$result;
    }

    /**
     * @param string $email
     * @param string $token
     * @param string $service
     * @param string $mandate
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function putMandate(string $email, string $token, string $service, string $mandate): void
    {
        $tokens = $this->cacheService->get($email, []);
        if (!isset($tokens[$token])) {
            throw new Exception('Not logged in', 401);
        }
        $memoryToken = $this->cacheService->get(self::PREFIX_TOKEN . $token, []);
        if (!isset($memoryToken[$service])) {
            $memoryToken[$service] = $mandate;
        }
        $this->cacheService->set($email, $tokens, $this->ttl);
        $this->cacheService->set(self::PREFIX_TOKEN . $token, $memoryToken, $this->ttl);
        $this->cacheService->set(self::PREFIX_MANDATE . $mandate, true, $this->ttl);
    }

    /**
     * @param string $email
     * @param string $token
     * @param string $service
     * @return string|null
     * @throws InvalidArgumentException
     */
    public function getMandate(string $email, string $token, string $service): ?string
    {
        $tokens = $this->cacheService->get($email, []);
        if (empty($tokens) || !isset($tokens[$token])) {
            return null;
        }
        $memoryToken = $this->cacheService->get(self::PREFIX_TOKEN . $token, []);
        if (empty($memoryToken) || empty($memoryToken[$service])) {
            return null;
        }
        if (empty($this->cacheService->get(self::PREFIX_MANDATE . $memoryToken[$service], false))) {
            return null;
        }
        $this->cacheService->set($email, $tokens, $this->ttl);
        $this->cacheService->set(self::PREFIX_TOKEN . $token, $memoryToken, $this->ttl);
        $this->cacheService->set(self::PREFIX_MANDATE . $memoryToken[$service], true, $this->ttl);
        return $memoryToken[$service];
    }

    /**
     * @param string $email
     * @throws InvalidArgumentException
     */
    public function removeEmail(string $email): void
    {
        $this->cacheService->delete($email);
    }

    /**
     * @param string $email
     * @param string $token
     * @throws InvalidArgumentException
     */
    public function removeToken(string $email, string $token): void
    {
        $tokens = $this->cacheService->get($email, []);
        if (empty($tokens)) {
            return;
        }
        unset($tokens[$token]);
        $this->cacheService->delete(self::PREFIX_TOKEN . $token);
        $this->cacheService->set($email, $tokens, $this->ttl);
    }

}
