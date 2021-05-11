<?php

namespace EgalFramework\APIContainer\Tests\Samples;

/**
 * Class TestAbstractClass
 *
 * This is an abstract class
 * @package EgalFramework\APIContainer\Tests\Samples
 */
class TestAbstractClass
{

    /**
     * Test abstract action
     * @roles www
     */
    public function actionAbstract()
    {

    }

    /**
     * This method should be overridden
     * @roles nobody
     */
    public function actionAbstractOverride()
    {

    }

    /**
     * This is protected abstract method
     */
    protected function protectedAbstract()
    {

    }

    /**
     * @param string $str
     * @roles www
     */
    protected function protectedAbstract2(string $str)
    {

    }

    /**
     * This is private abstract method
     */
    private function privateAbstract()
    {

    }

}
