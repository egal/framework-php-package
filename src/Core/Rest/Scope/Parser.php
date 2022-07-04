<?php

namespace Egal\Core\Rest\Scope;

use Egal\Core\Exceptions\ScopeParseException;

class Parser
{

    public function parse(?string $queryString): array
    {
        $scopes = [];

        if ($queryString === '' || $queryString === null) {
            return $scopes;
        }
        preg_match_all(ScopeFunction::REG_PATTERN, $queryString, $scopesRaw, PREG_SET_ORDER, 0);

        //TODO нужна др.реализация проверки queryString
        $scopesString = implode(ScopeFunction::SCOPES_DELIMITER, array_column($scopesRaw, 0));
        if ($scopesString !== $queryString) {
            throw new ScopeParseException();
        }

        foreach ($scopesRaw as $scopeRaw) {
            $parameters = $this->parseScopeParameters($scopeRaw);
            $scopes[] = ScopeFunction::make($scopeRaw['scope'], $parameters);
        }

        return $scopes;
    }

    private function makeValueFromRaw(string $value): string|int|bool|null|float
    {
        if ($value === 'true') {
            $value = true;
        } elseif ($value === 'false') {
            $value = false;
        } elseif ($value === 'null') {
            $value = null;
        } elseif (preg_match("/^'.+'$/", $value)) { // string type
            $value = ltrim(rtrim($value, "'"), "'");
        } elseif (preg_match("/^[1-9]+$/", $value)) { // int type
            $value = intval($value);
        } elseif (preg_match("/^[1-9]+\.[1-9]+$/", $value)) { // float type
            $value = floatval($value);
        } else {
            throw new ScopeParseException();
        }
        return $value;
    }

    private function parseScopeParameters(array $scope): array
    {
        $parameters = [];
        if (array_key_exists('parameters', $scope) && $scope['parameters'] != null) {
            foreach (explode(ScopeFunction::PARAMETERS_DELIMITER, $scope['parameters']) as $parameterString) {

                if (preg_match(ScopeFunction::PARAMETER_REG_PATTERN, $parameterString, $parameter)) {
                    $parameters[] = [
                        'key' => $parameter['key'],
                        'value' => $this->makeValueFromRaw($parameter['value'])
                    ];
                } else {
                    throw new ScopeParseException();
                }
            }
        }
        return $parameters;
    }
}
