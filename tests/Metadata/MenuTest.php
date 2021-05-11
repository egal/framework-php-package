<?php

namespace EgalFramework\Metadata\Tests;

use EgalFramework\Common\Session;
use EgalFramework\Metadata\Menu;
use EgalFramework\Metadata\Tests\Samples\AppMenu;
use PHPUnit\Framework\TestCase;

class MenuTest extends TestCase
{

    public function testMenu()
    {
        Session::setMenu(new AppMenu);
        $menu = new Menu();
        $submenu = $menu->add('model1', 'Model1');
        $submenu->add('model2', 'Model2');
        $menu->add('model3', 'Model3');
        $this->assertEquals([
            [
                'label' => 'model1',
                'route' => 'Model1',
                'deep' => [
                    [
                        'label' => 'model2',
                        'route' => 'Model2',
                    ],
                ],
            ],
            [
                'label' => 'model3',
                'route' => 'Model3',
            ],
        ], $menu->build());
    }

    public function testEmptyMenu()
    {
        $menu = new Menu();
        $this->assertEquals([], $menu->build());
    }

}
