<?php

namespace Thruster\Component\Promise;

/**
 * Class LazyPromise
 *
 * @package Thruster\Component\Promise
 * @author  Aurimas Niekis <aurimas@niekis.lt>
 */
class LazyPromise implements ExtendedPromiseInterface, CancellablePromiseInterface
{
    /**
     * @var callable
     */
    private $factory;

    /**
     * @var Promise
     */
    private $promise;

    public function __construct(callable $factory)
    {
        $this->factory = $factory;
    }

    public function then(
        callable $onFulfilled = null,
        callable $onRejected = null,
        callable $onProgress = null
    ) : PromiseInterface {
        return $this->promise()->then($onFulfilled, $onRejected, $onProgress);
    }

    public function done(callable $onFulfilled = null, callable $onRejected = null, callable $onProgress = null)
    {
        return $this->promise()->done($onFulfilled, $onRejected, $onProgress);
    }

    public function otherwise(callable $onRejected) : ExtendedPromiseInterface
    {
        return $this->promise()->otherwise($onRejected);
    }

    public function always(callable $onFulfilledOrRejected) : ExtendedPromiseInterface
    {
        return $this->promise()->always($onFulfilledOrRejected);
    }

    public function progress(callable $onProgress) : ExtendedPromiseInterface
    {
        return $this->promise()->progress($onProgress);
    }

    public function cancel()
    {
        return $this->promise()->cancel();
    }

    private function promise()
    {
        if (null === $this->promise) {
            try {
                $this->promise = resolve(call_user_func($this->factory));
            } catch (\Exception $exception) {
                $this->promise = new RejectedPromise($exception);
            }
        }

        return $this->promise;
    }
}
