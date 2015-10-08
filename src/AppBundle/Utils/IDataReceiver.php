<?php

namespace AppBundle\Utils;

interface IDataReceiver
{
    public function load(array $urlPool, \Closure $successReponce);
}
