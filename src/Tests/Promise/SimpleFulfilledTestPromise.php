<?php

namespace Thruster\Component\Promise\Tests\Promise;

use Throwable;
use Thruster\Component\Promise\PromiseInterface;
use Thruster\Component\Promise\RejectedPromise;

/**
 * Class SimpleFulfilledTestPromise
 *
 * @package Thruster\Component\Promise\Tests\Promise
 * @author  Aurimas Niekis <aurimas@niekis.lt>
 */
class SimpleFulfilledTestPromise implements PromiseInterface
{
    public function then(
        callable $onFulfilled = null,
        callable $onRejected = null,
        callable $onProgress = null
    ) : PromiseInterface {
        try {
            if ($onFulfilled) {
                $onFulfilled('foo');
            }

            return new self('foo');
        } catch (Throwable $exception) {
            return new RejectedPromise($exception);
        }
    }
}
