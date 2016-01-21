<?php

namespace Thruster\Component\Promise\Tests\Promise;

use Throwable;
use Thruster\Component\Promise\PromiseInterface;
use Thruster\Component\Promise\RejectedPromise;

/**
 * Class SimpleRejectedTestPromise
 *
 * @package Thruster\Component\Promise\Tests\Promise
 * @author  Aurimas Niekis <aurimas@niekis.lt>
 */
class SimpleRejectedTestPromise implements PromiseInterface
{
    public function then(
        callable $onFulfilled = null,
        callable $onRejected = null,
        callable $onProgress = null
    ) : PromiseInterface {
        try {
            if ($onRejected) {
                $onRejected('foo');
            }

            return new self('foo');
        } catch (Throwable $exception) {
            return new RejectedPromise($exception);
        }
    }
}
