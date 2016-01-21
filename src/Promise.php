<?php

namespace Thruster\Component\Promise;

/**
 * Class Promise
 *
 * @package Thruster\Component\Promise
 * @author  Aurimas Niekis <aurimas@niekis.lt>
 */
class Promise implements ExtendedPromiseInterface, CancellablePromiseInterface
{
    /**
     * @var callable
     */
    private $canceller;

    private $result;

    /**
     * @var array
     */
    private $handlers;

    /**
     * @var array
     */
    private $progressHandlers;

    /**
     * @var int
     */
    private $requiredCancelRequests;

    /**
     * @var int
     */
    private $cancelRequests;

    public function __construct(callable $resolver, callable $canceller = null)
    {
        $this->handlers = [];
        $this->progressHandlers = [];

        $this->requiredCancelRequests = 0;
        $this->cancelRequests         = 0;

        $this->canceller = $canceller;
        $this->call($resolver);
    }

    public function then(
        callable $onFulfilled = null,
        callable $onRejected = null,
        callable $onProgress = null
    ) : PromiseInterface {
        if (null !== $this->result) {
            return $this->result()->then($onFulfilled, $onRejected, $onProgress);
        }

        if (null === $this->canceller) {
            return new static($this->resolver($onFulfilled, $onRejected, $onProgress));
        }

        $this->requiredCancelRequests++;

        return new static($this->resolver($onFulfilled, $onRejected, $onProgress), function () {
            if (++$this->cancelRequests < $this->requiredCancelRequests) {
                return;
            }

            $this->cancel();
        });
    }

    public function done(callable $onFulfilled = null, callable $onRejected = null, callable $onProgress = null)
    {
        if (null !== $this->result) {
            return $this->result()->done($onFulfilled, $onRejected, $onProgress);
        }

        $this->handlers[] = function (ExtendedPromiseInterface $promise) use ($onFulfilled, $onRejected) {
            $promise
                ->done($onFulfilled, $onRejected);
        };

        if ($onProgress) {
            $this->progressHandlers[] = $onProgress;
        }
    }

    public function otherwise(callable $onRejected) : ExtendedPromiseInterface
    {
        return $this->then(null, function ($reason) use ($onRejected) {
            if (!_checkTypehint($onRejected, $reason)) {
                return new RejectedPromise($reason);
            }

            return $onRejected($reason);
        });
    }

    public function always(callable $onFulfilledOrRejected) : ExtendedPromiseInterface
    {
        return $this->then(function ($value) use ($onFulfilledOrRejected) {
            return resolve($onFulfilledOrRejected())->then(function () use ($value) {
                return $value;
            });
        }, function ($reason) use ($onFulfilledOrRejected) {
            return resolve($onFulfilledOrRejected())->then(function () use ($reason) {
                return new RejectedPromise($reason);
            });
        });
    }

    public function progress(callable $onProgress) : ExtendedPromiseInterface
    {
        return $this->then(null, null, $onProgress);
    }

    public function cancel()
    {
        if (null === $this->canceller || null !== $this->result) {
            return;
        }

        $this->call($this->canceller);
    }

    private function resolver(callable $onFulfilled = null, callable $onRejected = null, callable $onProgress = null)
    {
        return function ($resolve, $reject, $notify) use ($onFulfilled, $onRejected, $onProgress) {
            if ($onProgress) {
                $progressHandler = function ($update) use ($notify, $onProgress) {
                    try {
                        $notify($onProgress($update));
                    } catch (\Exception $e) {
                        $notify($e);
                    }
                };
            } else {
                $progressHandler = $notify;
            }

            $this->handlers[] = function (ExtendedPromiseInterface $promise) use (
                $onFulfilled,
                $onRejected,
                $resolve,
                $reject,
                $progressHandler
            ) {
                $promise
                    ->then($onFulfilled, $onRejected)
                    ->done($resolve, $reject, $progressHandler);
            };

            $this->progressHandlers[] = $progressHandler;
        };
    }

    private function resolve($value = null)
    {
        if (null !== $this->result) {
            return;
        }

        $this->settle(resolve($value));
    }

    private function reject($reason = null)
    {
        if (null !== $this->result) {
            return;
        }

        $this->settle(reject($reason));
    }

    private function notify($update = null)
    {
        if (null !== $this->result) {
            return;
        }

        foreach ($this->progressHandlers as $handler) {
            $handler($update);
        }
    }

    private function settle(ExtendedPromiseInterface $promise)
    {
        $handlers = $this->handlers;

        $this->progressHandlers = $this->handlers = [];
        $this->result           = $promise;

        foreach ($handlers as $handler) {
            $handler($promise);
        }
    }

    private function result()
    {
        while ($this->result instanceof self && null !== $this->result->result) {
            $this->result = $this->result->result;
        }

        return $this->result;
    }

    private function call(callable $callback)
    {
        try {
            $callback(
                function ($value = null) {
                    $this->resolve($value);
                },
                function ($reason = null) {
                    $this->reject($reason);
                },
                function ($update = null) {
                    $this->notify($update);
                }
            );
        } catch (\Exception $e) {
            $this->reject($e);
        }
    }
}