<?php

declare(strict_types=1);

namespace Egal\Validation;

use Egal\Validation\Exceptions\RegistrationValidationRuleException;
use Egal\Validation\Rules\LowerCaseRule;
use Egal\Validation\Rules\Rule;
use Egal\Validation\Rules\UpperCaseRule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider as IlluminateServiceProvider;

class ServiceProvider extends IlluminateServiceProvider
{

    /**
     * @throws \Exception
     */
    public function boot()
    {
        $this->registerRules([
            UpperCaseRule::class,
            LowerCaseRule::class,
        ]);
        $this->registerCustomRules();
    }

    /**
     * @throws \Exception
     */
    protected function registerCustomRules()
    {
        $dir = base_path('app/Rules');
        if (!is_dir($dir)) {
            return;
        }
        $classesFilesNames = array_filter(scandir($dir), function ($value) {
            return $value !== '.' && $value !== '..' && str_contains($value, '.php');
        });
        $classesNames = array_map(function ($value) {
            return 'App\\Rules\\' . str_replace('.php', '', $value);
        }, $classesFilesNames);
        $this->registerRules($classesNames);
    }

    /**
     * @param string $class
     * @throws \Exception
     */
    protected function registerRule(string $class)
    {
        if ($class === Rule::class || !is_a($class, Rule::class, true)) {
            throw new RegistrationValidationRuleException('Registration error ' . $class . ' validation rule!');
        }
        /** @var Rule $classInstance */
        $classInstance = new $class();
        Validator::extend($classInstance->getRule(), $classInstance->getCallback(), $classInstance->message());
    }

    /**
     * @param string[] $rulesClasses
     * @throws \Exception
     */
    protected function registerRules(array $rulesClasses)
    {
        foreach ($rulesClasses as $ruleClass) {
            $this->registerRule($ruleClass);
        }
    }

}
