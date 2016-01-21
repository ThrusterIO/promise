<?php

namespace Thruster\Component\Promise;

/**
 * Interface PromiseInterface
 *
 * @package Thruster\Component\Promise
 * @author  Aurimas Niekis <aurimas@niekis.lt>
 */
interface PromiseInterface
{
    /**
     * @return PromiseInterface
     */
    public function then(
        callable $onFulfilled = null,
        callable $onRejected = null,
        callable $onProgress = null
    ) : PromiseInterface;
}
