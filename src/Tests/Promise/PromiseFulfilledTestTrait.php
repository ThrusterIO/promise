<?php

namespace Thruster\Component\Promise\Tests\Promise;

use Thruster\Component\Promise\Tests\TestCase;
use Thruster\Component\Promise\Tests\PromiseAdapter\PromiseAdapterInterface;
use function Thruster\Component\Promise\resolve;
use function Thruster\Component\Promise\reject;

/**
 * Trait PromiseFulfilledTestTrait
 *
 * @package Thruster\Component\Promise\Tests\Promise
 * @author  Aurimas Niekis <aurimas@niekis.lt>
 */
trait PromiseFulfilledTestTrait
{
    /**
     * @return PromiseAdapterInterface
     */
    abstract public function getPromiseTestAdapter(callable $canceller = null);

    public function testFulfilledPromiseShouldBeImmutable()
    {
        $adapter = $this->getPromiseTestAdapter();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method(TestCase::MOCK_FUNCTION)
            ->with($this->identicalTo(1));

        $adapter->resolve(1);
        $adapter->resolve(2);

        $adapter->promise()
            ->then(
                $this->getCallable($mock),
                $this->expectCallableNever()
            );
    }

    public function testFulfilledPromiseShouldInvokeNewlyAddedCallback()
    {
        $adapter = $this->getPromiseTestAdapter();

        $adapter->resolve(1);

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method(TestCase::MOCK_FUNCTION)
            ->with($this->identicalTo(1));

        $adapter->promise()
            ->then($this->getCallable($mock), $this->expectCallableNever());
    }

    public function testThenShouldForwardResultWhenCallbackIsNull()
    {
        $adapter = $this->getPromiseTestAdapter();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method(TestCase::MOCK_FUNCTION)
            ->with($this->identicalTo(1));

        $adapter->resolve(1);
        $adapter->promise()
            ->then(
                null,
                $this->expectCallableNever()
            )
            ->then(
                $this->getCallable($mock),
                $this->expectCallableNever()
            );
    }

    public function testThenShouldForwardCallbackResultToNextCallback()
    {
        $adapter = $this->getPromiseTestAdapter();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method(TestCase::MOCK_FUNCTION)
            ->with($this->identicalTo(2));

        $adapter->resolve(1);
        $adapter->promise()
            ->then(
                function ($val) {
                    return $val + 1;
                },
                $this->expectCallableNever()
            )
            ->then(
                $this->getCallable($mock),
                $this->expectCallableNever()
            );
    }

    public function testThenShouldForwardPromisedCallbackResultValueToNextCallback()
    {
        $adapter = $this->getPromiseTestAdapter();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method(TestCase::MOCK_FUNCTION)
            ->with($this->identicalTo(2));

        $adapter->resolve(1);
        $adapter->promise()
            ->then(
                function ($val) {
                    return resolve($val + 1);
                },
                $this->expectCallableNever()
            )
            ->then(
                $this->getCallable($mock),
                $this->expectCallableNever()
            );
    }

    public function testThenShouldSwitchFromCallbacksToErrbacksWhenCallbackReturnsARejection()
    {
        $adapter = $this->getPromiseTestAdapter();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method(TestCase::MOCK_FUNCTION)
            ->with($this->identicalTo(2));

        $adapter->resolve(1);
        $adapter->promise()
            ->then(
                function ($val) {
                    return reject($val + 1);
                },
                $this->expectCallableNever()
            )
            ->then(
                $this->expectCallableNever(),
                $this->getCallable($mock)
            );
    }

    public function testThenShouldSwitchFromCallbacksToErrbacksWhenCallbackThrows()
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

        $adapter->resolve(1);
        $adapter->promise()
            ->then(
                $this->getCallable($mock),
                $this->expectCallableNever()
            )
            ->then(
                $this->expectCallableNever(),
                $this->getCallable($mock2)
            );
    }

    public function testCancelShouldReturnNullForFulfilledPromise()
    {
        $adapter = $this->getPromiseTestAdapter();

        $adapter->resolve();

        $this->assertNull($adapter->promise()->cancel());
    }

    public function testCancelShouldHaveNoEffectForFulfilledPromise()
    {
        $adapter = $this->getPromiseTestAdapter($this->expectCallableNever());

        $adapter->resolve();

        $adapter->promise()->cancel();
    }

    public function testDoneShouldInvokeFulfillmentHandlerForFulfilledPromise()
    {
        $adapter = $this->getPromiseTestAdapter();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method(TestCase::MOCK_FUNCTION)
            ->with($this->identicalTo(1));

        $adapter->resolve(1);
        $this->assertNull($adapter->promise()->done($this->getCallable($mock)));
    }

    public function testDoneShouldThrowExceptionThrownFulfillmentHandlerForFulfilledPromise()
    {
        $adapter = $this->getPromiseTestAdapter();

        $this->setExpectedException('\Exception', 'UnhandledRejectionException');

        $adapter->resolve(1);
        $this->assertNull($adapter->promise()->done(function () {
            throw new \Exception('UnhandledRejectionException');
        }));
    }

    public function testDoneShouldThrowUnhandledRejectionExceptionWhenFulfillmentHandlerRejectsForFulfilledPromise()
    {
        $adapter = $this->getPromiseTestAdapter();

        $this->setExpectedException('Thruster\\Component\\Promise\\Exception\\UnhandledRejectionException');

        $adapter->resolve(1);
        $this->assertNull($adapter->promise()->done(function () {
            return reject();
        }));
    }

    public function testOtherwiseShouldNotInvokeRejectionHandlerForFulfilledPromise()
    {
        $adapter = $this->getPromiseTestAdapter();

        $adapter->resolve(1);
        $adapter->promise()->otherwise($this->expectCallableNever());
    }

    public function testAlwaysShouldNotSuppressValueForFulfilledPromise()
    {
        $adapter = $this->getPromiseTestAdapter();

        $value = new \stdClass();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method(TestCase::MOCK_FUNCTION)
            ->with($this->identicalTo($value));

        $adapter->resolve($value);
        $adapter->promise()
            ->always(function () {
            })
            ->then($this->getCallable($mock));
    }

    public function testAlwaysShouldNotSuppressValueWhenHandlerReturnsANonPromiseForFulfilledPromise()
    {
        $adapter = $this->getPromiseTestAdapter();

        $value = new \stdClass();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method(TestCase::MOCK_FUNCTION)
            ->with($this->identicalTo($value));

        $adapter->resolve($value);
        $adapter->promise()
            ->always(function () {
                return 1;
            })
            ->then($this->getCallable($mock));
    }

    public function testAlwaysShouldNotSuppressValueWhenHandlerReturnsAPromiseForFulfilledPromise()
    {
        $adapter = $this->getPromiseTestAdapter();

        $value = new \stdClass();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method(TestCase::MOCK_FUNCTION)
            ->with($this->identicalTo($value));

        $adapter->resolve($value);
        $adapter->promise()
            ->always(function () {
                return resolve(1);
            })
            ->then($this->getCallable($mock));
    }

    public function testAlwaysShouldRejectWhenHandlerThrowsForFulfilledPromise()
    {
        $adapter = $this->getPromiseTestAdapter();

        $exception = new \Exception();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method(TestCase::MOCK_FUNCTION)
            ->with($this->identicalTo($exception));

        $adapter->resolve(1);
        $adapter->promise()
            ->always(function () use ($exception) {
                throw $exception;
            })
            ->then(null, $this->getCallable($mock));
    }

    public function testAlwaysShouldRejectWhenHandlerRejectsForFulfilledPromise()
    {
        $adapter = $this->getPromiseTestAdapter();

        $exception = new \Exception();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method(TestCase::MOCK_FUNCTION)
            ->with($this->identicalTo($exception));

        $adapter->resolve(1);
        $adapter->promise()
            ->always(function () use ($exception) {
                return reject($exception);
            })
            ->then(null, $this->getCallable($mock));
    }
}
