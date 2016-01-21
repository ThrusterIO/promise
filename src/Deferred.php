<?php

namespace Thruster\Component\Promise;

/**
 * Class Deferred
 *
 * @package Thruster\Component\Promise
 * @author  Aurimas Niekis <aurimas@niekis.lt>
 */
class Deferred implements PromisorInterface
{
    /**
     * @var Promise
     */
    private $promise;

    /**
     * @var callable
     */
    private $resolveCallback;

    /**
     * @var callable
     */
    private $rejectCallback;

    /**
     * @var callable
     */
    private $notifyCallback;

    /**
     * @var callable
     */
    private $canceller;

    public function __construct(callable $canceller = null)
    {
        $this->canceller = $canceller;
    }

    public function promise() : PromiseInterface
    {
        if (null === $this->promise) {
            $this->promise = new Promise(function ($resolve, $reject, $notify) {
                $this->resolveCallback = $resolve;
                $this->rejectCallback  = $reject;
                $this->notifyCallback  = $notify;
            }, $this->canceller);
        }

        return $this->promise;
    }

    public function resolve($value = null)
    {
        $this->promise();

        call_user_func($this->resolveCallback, $value);
    }

    public function reject($reason = null)
    {
        $this->promise();

        call_user_func($this->rejectCallback, $reason);
    }

    public function notify($update = null)
    {
        $this->promise();

        call_user_func($this->notifyCallback, $update);
    }
}
