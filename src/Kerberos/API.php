<?php

namespace EgalFramework\Kerberos;

use EgalFramework\Common\Interfaces\Kerberos\KDCResponseInterface;
use EgalFramework\Common\Interfaces\Kerberos\KerberosInterface;
use EgalFramework\Common\Interfaces\Kerberos\MandateInterface;
use EgalFramework\Common\Interfaces\Kerberos\UserInterface;
use EgalFramework\Kerberos\Exceptions\IncorrectDataException;
use EgalFramework\Kerberos\Exceptions\MandateInvalidException;
use EgalFramework\Kerberos\Exceptions\TimeShiftException;

/**
 * Class Client
 * @package EgalFramework\Kerberos
 */
class API implements KerberosInterface
{

    private Crypt $crypt;

    public function __construct()
    {
        $this->crypt = new Crypt;
    }

    public function getClientRequest(string $email, string $password): ClientRequest
    {
        return new ClientRequest($email, $this->crypt->encrypt(time(), $password));
    }

    /**
     * @param array $data
     * @return ClientRequest
     * @throws Exceptions\IncorrectDataException
     */
    public function getClientRequestFromArray(array $data): ClientRequest
    {
        $clientRequest = new ClientRequest();
        $clientRequest->fromArray($data);
        return $clientRequest;
    }

    /**
     * @param ClientRequest $request
     * @param UserInterface $user
     * @param string $serverPassword
     * @return KDCResponse
     * @throws IncorrectDataException
     */
    public function createKDCResponse(
        ClientRequest $request,
        UserInterface $user,
        string $serverPassword
    ): KDCResponse
    {
        $time = $this->crypt->decrypt($request->getData(), $user->password);
        if (!$time) {
            throw new IncorrectDataException('Data can\'t be verified', 401);
        }
        $sessionKey = new SessionKey($request->getEmail(), $this->crypt->encrypt(time(), $user->password));
        $mandateData = new Mandate(
            hash(Common::HASH_ALGORITHM, json_encode($sessionKey->toArray())),
            new MandateData($user->toArray(), $user->getRolesArray())
        );
        return new KDCResponse(
            $sessionKey,
            $this->crypt->encrypt(json_encode($mandateData->toArray()), $serverPassword)
        );
    }

    /**
     * @param ClientRequest $request
     * @param UserInterface $user
     * @throws IncorrectDataException
     * @throws TimeShiftException
     */
    public function checkClientRequest(ClientRequest $request, UserInterface $user)
    {
        $this->checkTime(
            $this->crypt->decrypt($request->getData(), $user->password)
        );
    }

    /**
     * @param string $time
     * @throws TimeShiftException
     */
    private function checkTime(string $time): void
    {
        if (($time < time() - 300) || ($time > time() + 300)) {
            throw new TimeShiftException('Time shift', 401);
        }
    }

    /**
     * @param KDCResponse $response
     * @param UserInterface $user
     * @throws IncorrectDataException
     * @throws TimeShiftException
     */
    public function checkKDCResponse(KDCResponse $response, UserInterface $user): void
    {
        $this->checkTime(
            $this->crypt->decrypt($response->getSessionKey()->getData(), $user->password)
        );
    }

    /**
     * @param string $encryptedData
     * @param string $password
     * @return Mandate
     * @throws IncorrectDataException
     * @throws MandateInvalidException
     */
    public function getMandate(string $encryptedData, string $password): MandateInterface
    {
        $data = $this->crypt->decrypt($encryptedData, $password);
        if (!$data) {
            throw new MandateInvalidException('Failed to decrypt data', 401);
        }
        $mandateData = json_decode($data, true);
        if (json_last_error()) {
            throw  new MandateInvalidException('Failed to decode JSON: ' . json_last_error_msg(), 401);
        }
        $mandateDataObj = new MandateData;
        $mandateDataObj->fromArray($mandateData[Common::FIELD_DATA]);
        return new Mandate(
            $mandateData[Common::FIELD_SESSION_KEY],
            $mandateDataObj
        );
    }

    public function getNewKDCResponse(): KDCResponseInterface
    {
        return new KDCResponse;
    }

}
