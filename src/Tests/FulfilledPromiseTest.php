<?php

namespace Thruster\Component\Promise\Tests;

use Thruster\Component\Promise\FulfilledPromise;
use Thruster\Component\Promise\Tests\Promise\PromiseFulfilledTestTrait;
use Thruster\Component\Promise\Tests\Promise\PromiseSettledTestTrait;
use Thruster\Component\Promise\Tests\PromiseAdapter\CallbackPromiseAdapter;

/**
 * Class FulfilledPromiseTest
 *
 * @package Thruster\Component\Promise\Tests
 * @author  Aurimas Niekis <aurimas@niekis.lt>
 */
class FulfilledPromiseTest extends TestCase
{
    use PromiseSettledTestTrait,
        PromiseFulfilledTestTrait;

    public function getPromiseTestAdapter(callable $canceller = null)
    {
        $promise = null;

        return new CallbackPromiseAdapter([
            'promise' => function () use (&$promise) {
                if (!$promise) {
                    throw new \LogicException('FulfilledPromise must be resolved before obtaining the promise');
                }

                return $promise;
            },
            'resolve' => function ($value = null) use (&$promise) {
                if (!$promise) {
                    $promise = new FulfilledPromise($value);
                }
            },
            'reject' => function () {
                throw new \LogicException('You cannot call reject() for Thruster\Component\Promise\FulfilledPromise');
            },
            'notify' => function () {
                // no-op
            },
            'settle' => function ($value = null) use (&$promise) {
                if (!$promise) {
                    $promise = new FulfilledPromise($value);
                }
            },
        ]);
    }

    public function testShouldThrowExceptionIfConstructedWithAPromise()
    {
        $this->setExpectedException('\InvalidArgumentException');

        return new FulfilledPromise(new FulfilledPromise());
    }
}
