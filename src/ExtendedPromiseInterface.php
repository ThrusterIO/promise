<?php

namespace Thruster\Component\Promise;

/**
 * Interface ExtendedPromiseInterface
 *
 * @package Thruster\Component\Promise
 * @author  Aurimas Niekis <aurimas@niekis.lt>
 */
interface ExtendedPromiseInterface extends PromiseInterface
{
    /**
     * @return void
     */
    public function done(callable $onFulfilled = null, callable $onRejected = null, callable $onProgress = null);

    /**
     * @return ExtendedPromiseInterface
     */
    public function otherwise(callable $onRejected) : ExtendedPromiseInterface;

    /**
     * @return ExtendedPromiseInterface
     */
    public function always(callable $onFulfilledOrRejected) : ExtendedPromiseInterface;

    /**
     * @return ExtendedPromiseInterface
     */
    public function progress(callable $onProgress) : ExtendedPromiseInterface;
}
