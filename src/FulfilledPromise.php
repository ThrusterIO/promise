<?php

namespace Thruster\Component\Promise;

/**
 * Class FulfilledPromise
 *
 * @package Thruster\Component\Promise
 * @author  Aurimas Niekis <aurimas@niekis.lt>
 */
class FulfilledPromise implements ExtendedPromiseInterface, CancellablePromiseInterface
{
    private $value;

    public function __construct($value = null)
    {
        if ($value instanceof PromiseInterface) {
            throw new \InvalidArgumentException(
                'You cannot create Thruster\Component\Promise\FulfilledPromise with a promise.' .
                ' Use Thruster\Component\Promise\resolve($promiseOrValue) instead.'
            );
        }

        $this->value = $value;
    }

    public function then(
        callable $onFulfilled = null,
        callable $onRejected = null,
        callable $onProgress = null
    ) : PromiseInterface {
        if (null === $onFulfilled) {
            return $this;
        }

        try {
            return resolve($onFulfilled($this->value));
        } catch (\Exception $exception) {
            return new RejectedPromise($exception);
        }
    }

    public function done(callable $onFulfilled = null, callable $onRejected = null, callable $onProgress = null)
    {
        if (null === $onFulfilled) {
            return;
        }

        $result = $onFulfilled($this->value);

        if ($result instanceof ExtendedPromiseInterface) {
            $result->done();
        }
    }

    public function otherwise(callable $onRejected) : ExtendedPromiseInterface
    {
        return $this;
    }

    public function always(callable $onFulfilledOrRejected) : ExtendedPromiseInterface
    {
        return $this->then(function ($value) use ($onFulfilledOrRejected) {
            return resolve($onFulfilledOrRejected())->then(function () use ($value) {
                return $value;
            });
        });
    }

    public function progress(callable $onProgress) : ExtendedPromiseInterface
    {
        return $this;
    }

    public function cancel()
    {
    }
}
