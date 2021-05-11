<?php

namespace EgalFramework\APIContainer\Tests\Samples;

/**
 * Trait TestTrait
 *
 * There is a trait description
 * @package EgalFramework\APIContainer\Tests\Samples
 */
trait TestTrait
{

    /**
     * A trait action
     *
     * With description
     * @param string $qqq
     * @roles admin
     */
    public function actionTrait(string $qqq)
    {

    }

    /**
     * Protected trait method
     */
    protected function protectedTraitMethod()
    {

    }

    /**
     * Private trait method
     * @roles nobody , admin
     */
    private function privateTraitMethod()
    {

    }

}
