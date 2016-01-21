<?php

namespace Thruster\Component\Promise\Tests;

use Thruster\Component\Promise\RejectedPromise;
use Thruster\Component\Promise\Tests\Promise\PromiseRejectedTestTrait;
use Thruster\Component\Promise\Tests\Promise\PromiseSettledTestTrait;
use Thruster\Component\Promise\Tests\PromiseAdapter\CallbackPromiseAdapter;

/**
 * Class RejectedPromiseTest
 *
 * @package Thruster\Component\Promise\Tests
 * @author  Aurimas Niekis <aurimas@niekis.lt>
 */
class RejectedPromiseTest extends TestCase
{
    use PromiseSettledTestTrait,
        PromiseRejectedTestTrait;

    public function getPromiseTestAdapter(callable $canceller = null)
    {
        $promise = null;

        return new CallbackPromiseAdapter([
            'promise' => function () use (&$promise) {
                if (!$promise) {
                    throw new \LogicException('RejectedPromise must be rejected before obtaining the promise');
                }

                return $promise;
            },
            'resolve' => function () {
                throw new \LogicException('You cannot call resolve() for Thruster\Component\Promise\RejectedPromise');
            },
            'reject' => function ($reason = null) use (&$promise) {
                if (!$promise) {
                    $promise = new RejectedPromise($reason);
                }
            },
            'notify' => function () {
                // no-op
            },
            'settle' => function ($reason = null) use (&$promise) {
                if (!$promise) {
                    $promise = new RejectedPromise($reason);
                }
            },
        ]);
    }

    public function testShouldThrowExceptionIfConstructedWithAPromise()
    {
        $this->setExpectedException('\InvalidArgumentException');

        return new RejectedPromise(new RejectedPromise());
    }
}
