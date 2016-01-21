<?php

namespace Thruster\Component\Promise\Tests;

use Thruster\Component\Promise\Promise;
use Thruster\Component\Promise\Tests\Promise\FullTestTrait;
use Thruster\Component\Promise\Tests\Promise\SimpleFulfilledTestPromise;
use Thruster\Component\Promise\Tests\Promise\SimpleRejectedTestPromise;
use Thruster\Component\Promise\Tests\PromiseAdapter\CallbackPromiseAdapter;

/**
 * Class PromiseTest
 *
 * @package Thruster\Component\Promise\Tests\Promise
 * @author  Aurimas Niekis <aurimas@niekis.lt>
 */
class PromiseTest extends TestCase
{
    use FullTestTrait;

    public function getPromiseTestAdapter(callable $canceller = null)
    {
        $resolveCallback = $rejectCallback = $progressCallback = null;

        $promise = new Promise(
            function ($resolve, $reject, $progress) use (&$resolveCallback, &$rejectCallback, &$progressCallback) {
                $resolveCallback  = $resolve;
                $rejectCallback   = $reject;
                $progressCallback = $progress;
            },
            $canceller
        );

        return new CallbackPromiseAdapter([
            'promise' => function () use ($promise) {
                return $promise;
            },
            'resolve' => $resolveCallback,
            'reject'  => $rejectCallback,
            'notify'  => $progressCallback,
            'settle'  => $resolveCallback,
        ]);
    }

    public function testShouldRejectIfResolverThrowsException()
    {
        $exception = new \Exception('foo');

        $promise = new Promise(function () use ($exception) {
            throw $exception;
        });

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method(TestCase::MOCK_FUNCTION)
            ->with($this->identicalTo($exception));

        $promise
            ->then($this->expectCallableNever(), $this->getCallable($mock));
    }

    public function testShouldFulfillIfFullfilledWithSimplePromise()
    {
        $adapter = $this->getPromiseTestAdapter();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method(TestCase::MOCK_FUNCTION)
            ->with($this->identicalTo('foo'));

        $adapter->promise()
            ->then($this->getCallable($mock));

        $adapter->resolve(new SimpleFulfilledTestPromise());
    }

    public function testShouldRejectIfRejectedWithSimplePromise()
    {
        $adapter = $this->getPromiseTestAdapter();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method(TestCase::MOCK_FUNCTION)
            ->with($this->identicalTo('foo'));

        $adapter->promise()
            ->then($this->expectCallableNever(), $this->getCallable($mock));

        $adapter->resolve(new SimpleRejectedTestPromise());
    }
}
