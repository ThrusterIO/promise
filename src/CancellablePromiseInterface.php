<?php

namespace Thruster\Component\Promise;

/**
 * Interface CancellablePromiseInterface
 *
 * @package Thruster\Component\Promise
 * @author  Aurimas Niekis <aurimas@niekis.lt>
 */
interface CancellablePromiseInterface extends PromiseInterface
{
    /**
     * @return void
     */
    public function cancel();
}
