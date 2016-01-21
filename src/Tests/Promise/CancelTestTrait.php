<?php

namespace Thruster\Component\Promise\Tests\Promise;

use Thruster\Component\Promise\Deferred;
use Thruster\Component\Promise\Promise;
use Thruster\Component\Promise\Tests\PromiseAdapter\PromiseAdapterInterface;
use Thruster\Component\Promise\Tests\TestCase;

/**
 * Trait CancelTestTrait
 *
 * @package Thruster\Component\Promise\Tests\Promise
 * @author  Aurimas Niekis <aurimas@niekis.lt>
 */
trait CancelTestTrait
{
    /**
     * @return PromiseAdapterInterface
     */
    abstract public function getPromiseTestAdapter(callable $canceller = null);

    public function testCancelShouldCallCancellerWithResolverArguments()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method(TestCase::MOCK_FUNCTION)
            ->with($this->isType('callable'), $this->isType('callable'), $this->isType('callable'));

        $adapter = $this->getPromiseTestAdapter($this->getCallable($mock));

        $adapter->promise()->cancel();
    }

    public function testCancelShouldFulfillPromiseIfCancellerFulfills()
    {
        $adapter = $this->getPromiseTestAdapter(function ($resolve) {
            $resolve(1);
        });

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method(TestCase::MOCK_FUNCTION)
            ->with($this->identicalTo(1));

        $adapter->promise()
            ->then($this->getCallable($mock), $this->expectCallableNever());

        $adapter->promise()->cancel();
    }

    public function testCancelShouldRejectPromiseIfCancellerRejects()
    {
        $adapter = $this->getPromiseTestAdapter(function ($resolve, $reject) {
            $reject(1);
        });

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method(TestCase::MOCK_FUNCTION)
            ->with($this->identicalTo(1));

        $adapter->promise()
            ->then($this->expectCallableNever(), $this->getCallable($mock));

        $adapter->promise()->cancel();
    }

    public function testCancelShouldRejectPromiseWithExceptionIfCancellerThrows()
    {
        $e = new \Exception();

        $adapter = $this->getPromiseTestAdapter(function () use ($e) {
            throw $e;
        });

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method(TestCase::MOCK_FUNCTION)
            ->with($this->identicalTo($e));

        $adapter->promise()
            ->then($this->expectCallableNever(), $this->getCallable($mock));

        $adapter->promise()->cancel();
    }

    public function testCancelShouldProgressPromiseIfCancellerNotifies()
    {
        $adapter = $this->getPromiseTestAdapter(function ($resolve, $reject, $progress) {
            $progress(1);
        });

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method(TestCase::MOCK_FUNCTION)
            ->with($this->identicalTo(1));

        $adapter->promise()
            ->then($this->expectCallableNever(), $this->expectCallableNever(), $this->getCallable($mock));

        $adapter->promise()->cancel();
    }

    public function testCancelShouldCallCancellerOnlyOnceIfCancellerResolves()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method(TestCase::MOCK_FUNCTION)
            ->will($this->returnCallback(function ($resolve) {
                $resolve();
            }));

        $adapter = $this->getPromiseTestAdapter($this->getCallable($mock));

        $adapter->promise()->cancel();
        $adapter->promise()->cancel();
    }

    public function testCancelShouldHaveNoEffectIfCancellerDoesNothing()
    {
        $adapter = $this->getPromiseTestAdapter(function () {
        });

        $adapter->promise()
            ->then($this->expectCallableNever(), $this->expectCallableNever());

        $adapter->promise()->cancel();
        $adapter->promise()->cancel();
    }

    public function testCancelShouldCallCancellerFromDeepNestedPromiseChain()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method(TestCase::MOCK_FUNCTION);

        $adapter = $this->getPromiseTestAdapter($this->getCallable($mock));

        $promise = $adapter->promise()
            ->then(function () {
                return new Promise(function () {
                });
            })
            ->then(function () {
                $d = new Deferred();

                return $d->promise();
            })
            ->then(function () {
                return new Promise(function () {
                });
            });

        $promise->cancel();
    }

    public function testCancelCalledOnChildrenSouldOnlyCancelWhenAllChildrenCancelled()
    {
        $adapter = $this->getPromiseTestAdapter($this->expectCallableNever());

        $child1 = $adapter->promise()
            ->then()
            ->then();

        $adapter->promise()
            ->then();

        $child1->cancel();
    }

    public function testCancelShouldTriggerCancellerWhenAllChildrenCancel()
    {
        $adapter = $this->getPromiseTestAdapter($this->expectCallableOnce());

        $child1 = $adapter->promise()
            ->then()
            ->then();

        $child2 = $adapter->promise()
            ->then();

        $child1->cancel();
        $child2->cancel();
    }

    public function testCancelShouldAlwaysTriggerCancellerWhenCalledOnRootPromise()
    {
        $adapter = $this->getPromiseTestAdapter($this->expectCallableOnce());

        $adapter->promise()
            ->then()
            ->then();

        $adapter->promise()
            ->then();

        $adapter->promise()->cancel();
    }
}
