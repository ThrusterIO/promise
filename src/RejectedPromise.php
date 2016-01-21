<?php

namespace Thruster\Component\Promise;

use Thruster\Component\Promise\Exception\UnhandledRejectionException;

/**
 * Class RejectedPromise
 *
 * @package Thruster\Component\Promise
 * @author  Aurimas Niekis <aurimas@niekis.lt>
 */
class RejectedPromise implements ExtendedPromiseInterface, CancellablePromiseInterface
{
    private $reason;

    public function __construct($reason = null)
    {
        if ($reason instanceof PromiseInterface) {
            throw new \InvalidArgumentException(
                'You cannot create Thruster\Component\Promise\RejectedPromise with a promise.' .
                ' Use Thruster\Component\Promise\reject($promiseOrValue) instead.'
            );
        }

        $this->reason = $reason;
    }

    public function then(
        callable $onFulfilled = null,
        callable $onRejected = null,
        callable $onProgress = null
    ) : PromiseInterface {
        if (null === $onRejected) {
            return $this;
        }

        try {
            return resolve($onRejected($this->reason));
        } catch (\Exception $exception) {
            return new RejectedPromise($exception);
        }
    }

    public function done(callable $onFulfilled = null, callable $onRejected = null, callable $onProgress = null)
    {
        if (null === $onRejected) {
            throw UnhandledRejectionException::resolve($this->reason);
        }

        $result = $onRejected($this->reason);

        if ($result instanceof self) {
            throw UnhandledRejectionException::resolve($result->reason);
        }

        if ($result instanceof ExtendedPromiseInterface) {
            $result->done();
        }
    }

    public function otherwise(callable $onRejected) : ExtendedPromiseInterface
    {
        if (!_checkTypehint($onRejected, $this->reason)) {
            return $this;
        }

        return $this->then(null, $onRejected);
    }

    public function always(callable $onFulfilledOrRejected) : ExtendedPromiseInterface
    {
        return $this->then(null, function ($reason) use ($onFulfilledOrRejected) {
            return resolve($onFulfilledOrRejected())->then(function () use ($reason) {
                return new RejectedPromise($reason);
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
