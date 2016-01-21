<?php

namespace Thruster\Component\Promise;

/**
 * Interface PromisorInterface
 *
 * @package Thruster\Component\Promise
 * @author  Aurimas Niekis <aurimas@niekis.lt>
 */
interface PromisorInterface
{
    /**
     * @return PromiseInterface
     */
    public function promise() : PromiseInterface;
}
