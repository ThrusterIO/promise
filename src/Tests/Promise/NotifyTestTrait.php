<?php

namespace Thruster\Component\Promise\Tests\Promise;

use Thruster\Component\Promise\Tests\PromiseAdapter\PromiseAdapterInterface;
use Thruster\Component\Promise\Tests\TestCase;

/**
 * Trait NotifyTestTrait
 *
 * @package Thruster\Component\Promise\Tests\Promise
 * @author  Aurimas Niekis <aurimas@niekis.lt>
 */
trait NotifyTestTrait
{
    /**
     * @return PromiseAdapterInterface
     */
    abstract public function getPromiseTestAdapter(callable $canceller = null);

    public function testNotifyShouldProgress()
    {
        $adapter = $this->getPromiseTestAdapter();

        $sentinel = new \stdClass();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method(TestCase::MOCK_FUNCTION)
            ->with($sentinel);

        $adapter->promise()
            ->then($this->expectCallableNever(), $this->expectCallableNever(), $this->getCallable($mock));

        $adapter->notify($sentinel);
    }

    public function testNotifyShouldPropagateProgressToDownstreamPromises()
    {
        $adapter = $this->getPromiseTestAdapter();

        $sentinel = new \stdClass();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method(TestCase::MOCK_FUNCTION)
            ->will($this->returnArgument(0));

        $mock2 = $this->createCallableMock();
        $mock2
            ->expects($this->once())
            ->method(TestCase::MOCK_FUNCTION)
            ->with($sentinel);

        $adapter->promise()
            ->then(
                $this->expectCallableNever(),
                $this->expectCallableNever(),
                $this->getCallable($mock)
            )
            ->then(
                $this->expectCallableNever(),
                $this->expectCallableNever(),
                $this->getCallable($mock2)
            );

        $adapter->notify($sentinel);
    }

    public function testNotifyShouldPropagateTransformedProgressToDownstreamPromises()
    {
        $adapter = $this->getPromiseTestAdapter();

        $sentinel = new \stdClass();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method(TestCase::MOCK_FUNCTION)
            ->will($this->returnValue($sentinel));

        $mock2 = $this->createCallableMock();
        $mock2
            ->expects($this->once())
            ->method(TestCase::MOCK_FUNCTION)
            ->with($sentinel);

        $adapter->promise()
            ->then(
                $this->expectCallableNever(),
                $this->expectCallableNever(),
                $this->getCallable($mock)
            )
            ->then(
                $this->expectCallableNever(),
                $this->expectCallableNever(),
                $this->getCallable($mock2)
            );

        $adapter->notify(1);
    }

    public function testNotifyShouldPropagateCaughtExceptionValueAsProgress()
    {
        $adapter = $this->getPromiseTestAdapter();

        $exception = new \Exception();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method(TestCase::MOCK_FUNCTION)
            ->will($this->throwException($exception));

        $mock2 = $this->createCallableMock();
        $mock2
            ->expects($this->once())
            ->method(TestCase::MOCK_FUNCTION)
            ->with($this->identicalTo($exception));

        $adapter->promise()
            ->then(
                $this->expectCallableNever(),
                $this->expectCallableNever(),
                $this->getCallable($mock)
            )
            ->then(
                $this->expectCallableNever(),
                $this->expectCallableNever(),
                $this->getCallable($mock2)
            );

        $adapter->notify(1);
    }

    public function testNotifyShouldForwardProgressEventsWhenIntermediaryCallbackTiedToAResolvedPromiseReturnsAPromise()
    {
        $adapter = $this->getPromiseTestAdapter();
        $adapter2 = $this->getPromiseTestAdapter();

        $promise2 = $adapter2->promise();

        $sentinel = new \stdClass();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method(TestCase::MOCK_FUNCTION)
            ->with($sentinel);

        // resolve BEFORE attaching progress handler
        $adapter->resolve();

        $adapter->promise()
            ->then(function () use ($promise2) {
                return $promise2;
            })
            ->then(
                $this->expectCallableNever(),
                $this->expectCallableNever(),
                $this->getCallable($mock)
            );

        $adapter2->notify($sentinel);
    }

    public function testNotifyShouldForwardProgressEventsWhenIntermediaryClbckTiedToAnUnresolvedPromiseReturnsAPromise()
    {
        $adapter = $this->getPromiseTestAdapter();
        $adapter2 = $this->getPromiseTestAdapter();

        $promise2 = $adapter2->promise();

        $sentinel = new \stdClass();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method(TestCase::MOCK_FUNCTION)
            ->with($sentinel);

        $adapter->promise()
            ->then(function () use ($promise2) {
                return $promise2;
            })
            ->then(
                $this->expectCallableNever(),
                $this->expectCallableNever(),
                $this->getCallable($mock)
            );

        // resolve AFTER attaching progress handler
        $adapter->resolve();
        $adapter2->notify($sentinel);
    }

    public function testNotifyShouldForwardProgressWhenResolvedWithAnotherPromise()
    {
        $adapter = $this->getPromiseTestAdapter();
        $adapter2 = $this->getPromiseTestAdapter();

        $sentinel = new \stdClass();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method(TestCase::MOCK_FUNCTION)
            ->will($this->returnValue($sentinel));

        $mock2 = $this->createCallableMock();
        $mock2
            ->expects($this->once())
            ->method(TestCase::MOCK_FUNCTION)
            ->with($sentinel);

        $adapter->promise()
            ->then(
                $this->expectCallableNever(),
                $this->expectCallableNever(),
                $this->getCallable($mock)
            )
            ->then(
                $this->expectCallableNever(),
                $this->expectCallableNever(),
                $this->getCallable($mock2)
            );

        $adapter->resolve($adapter2->promise());
        $adapter2->notify($sentinel);
    }

    public function testNotifyShouldAllowResolveAfterProgress()
    {
        $adapter = $this->getPromiseTestAdapter();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->at(0))
            ->method(TestCase::MOCK_FUNCTION)
            ->with($this->identicalTo(1));
        $mock
            ->expects($this->at(1))
            ->method(TestCase::MOCK_FUNCTION)
            ->with($this->identicalTo(2));

        $adapter->promise()
            ->then(
                $this->getCallable($mock),
                $this->expectCallableNever(),
                $this->getCallable($mock)
            );

        $adapter->notify(1);
        $adapter->resolve(2);
    }

    public function testNotifyShouldAllowRejectAfterProgress()
    {
        $adapter = $this->getPromiseTestAdapter();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->at(0))
            ->method(TestCase::MOCK_FUNCTION)
            ->with($this->identicalTo(1));
        $mock
            ->expects($this->at(1))
            ->method(TestCase::MOCK_FUNCTION)
            ->with($this->identicalTo(2));

        $adapter->promise()
            ->then(
                $this->expectCallableNever(),
                $this->getCallable($mock),
                $this->getCallable($mock)
            );

        $adapter->notify(1);
        $adapter->reject(2);
    }

    public function testNotifyShouldReturnSilentlyOnProgressWhenAlreadyRejected()
    {
        $adapter = $this->getPromiseTestAdapter();

        $adapter->reject(1);

        $this->assertNull($adapter->notify());
    }

    public function testNotifyShouldInvokeProgressHandler()
    {
        $adapter = $this->getPromiseTestAdapter();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method(TestCase::MOCK_FUNCTION)
            ->with($this->identicalTo(1));

        $adapter->promise()->progress($this->getCallable($mock));
        $adapter->notify(1);
    }

    public function testNotifyShouldInvokeProgressHandlerFromDone()
    {
        $adapter = $this->getPromiseTestAdapter();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method(TestCase::MOCK_FUNCTION)
            ->with($this->identicalTo(1));

        $this->assertNull($adapter->promise()->done(null, null, $this->getCallable($mock)));
        $adapter->notify(1);
    }

    public function testNotifyShouldThrowExceptionThrownProgressHandlerFromDone()
    {
        $adapter = $this->getPromiseTestAdapter();

        $this->setExpectedException('\Exception', 'UnhandledRejectionException');

        $this->assertNull($adapter->promise()->done(null, null, function () {
            throw new \Exception('UnhandledRejectionException');
        }));
        $adapter->notify(1);
    }
}
