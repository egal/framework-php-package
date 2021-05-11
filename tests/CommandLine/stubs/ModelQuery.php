<?php

namespace EgalFramework\CommandLine\Tests\Stubs;

use EgalFramework\Common\Registry;
use EgalFramework\Common\Session;

class ModelQuery
{

    public function where()
    {
        return $this;
    }

    public function orderBy()
    {
        return $this;
    }

    public function limit()
    {
        return $this;
    }

    public function get()
    {
        return $this;
    }

    public function all()
    {
        $item = new Model;
        $item->id = (int)Session::getRegistry()->get('id');
        if ($item->id) {
            Session::getRegistry()->set('id', null);
        }
        return [$item];
    }

    public function update()
    {
    }

}
