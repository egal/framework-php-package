<?php

namespace EgalFramework\Common\Interfaces;

use Illuminate\Contracts\Cache\Repository;

interface MandateContainerInterface
{

    public function __construct(Repository $cacheService, int $ttl);

    public function putToken(string $email, string $token): void;

    public function putMandate(string $email, string $token, string $service, string $mandate): void;

    public function getMandate(string $email, string $token, string $service): ?string;

    public function removeToken(string $email, string $token): void;

    public function removeEmail(string $email): void;

}
