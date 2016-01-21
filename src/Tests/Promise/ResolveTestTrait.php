<?php

namespace Thruster\Component\Promise\Tests\Promise;

use Thruster\Component\Promise\Tests\TestCase;
use Thruster\Component\Promise\Tests\PromiseAdapter\PromiseAdapterInterface;
use function Thruster\Component\Promise\resolve;
use function Thruster\Component\Promise\reject;

/**
 * Trait ResolveTestTrait
 *
 * @package Thruster\Component\Promise\Tests\Promise
 * @author  Aurimas Niekis <aurimas@niekis.lt>
 */
trait ResolveTestTrait
{
    /**
     * @return PromiseAdapterInterface
     */
    abstract public function getPromiseTestAdapter(callable $canceller = null);

    public function testResolveShouldResolve()
    {
        $adapter = $this->getPromiseTestAdapter();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method(TestCase::MOCK_FUNCTION)
            ->with($this->identicalTo(1));

        $adapter->promise()
            ->then($this->getCallable($mock));

        $adapter->resolve(1);
    }

    public function testResolveShouldResolveWithPromisedValue()
    {
        $adapter = $this->getPromiseTestAdapter();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method(TestCase::MOCK_FUNCTION)
            ->with($this->identicalTo(1));

        $adapter->promise()
            ->then($this->getCallable($mock));

        $adapter->resolve(resolve(1));
    }

    public function testResolveShouldRejectWhenResolvedWithRejectedPromise()
    {
        $adapter = $this->getPromiseTestAdapter();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method(TestCase::MOCK_FUNCTION)
            ->with($this->identicalTo(1));

        $adapter->promise()
            ->then($this->expectCallableNever(), $this->getCallable($mock));

        $adapter->resolve(reject(1));
    }

    public function testResolveShouldForwardValueWhenCallbackIsNull()
    {
        $adapter = $this->getPromiseTestAdapter();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method(TestCase::MOCK_FUNCTION)
            ->with($this->identicalTo(1));

        $adapter->promise()
            ->then(
                null,
                $this->expectCallableNever()
            )
            ->then(
                $this->getCallable($mock),
                $this->expectCallableNever()
            );

        $adapter->resolve(1);
    }

    public function testResolveShouldMakePromiseImmutable()
    {
        $adapter = $this->getPromiseTestAdapter();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method(TestCase::MOCK_FUNCTION)
            ->with($this->identicalTo(1));

        $adapter->promise()
            ->then(function ($value) use ($adapter) {
                $adapter->resolve(3);

                return $value;
            })
            ->then(
                $this->getCallable($mock),
                $this->expectCallableNever()
            );

        $adapter->resolve(1);
        $adapter->resolve(2);
    }

    public function testDoneShouldInvokeFulfillmentHandler()
    {
        $adapter = $this->getPromiseTestAdapter();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method(TestCase::MOCK_FUNCTION)
            ->with($this->identicalTo(1));

        $this->assertNull($adapter->promise()->done($this->getCallable($mock)));
        $adapter->resolve(1);
    }

    public function testDoneShouldThrowExceptionThrownFulfillmentHandler()
    {
        $adapter = $this->getPromiseTestAdapter();

        $this->setExpectedException('\Exception', 'UnhandledRejectionException');

        $this->assertNull($adapter->promise()->done(function () {
            throw new \Exception('UnhandledRejectionException');
        }));
        $adapter->resolve(1);
    }

    public function testDoneShouldThrowUnhandledRejectionExceptionWhenFulfillmentHandlerRejects()
    {
        $adapter = $this->getPromiseTestAdapter();

        $this->setExpectedException('Thruster\\Component\\Promise\\Exception\\UnhandledRejectionException');

        $this->assertNull($adapter->promise()->done(function () {
            return reject();
        }));
        $adapter->resolve(1);
    }

    public function testAlwaysShouldNotSuppressValue()
    {
        $adapter = $this->getPromiseTestAdapter();

        $value = new \stdClass();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method(TestCase::MOCK_FUNCTION)
            ->with($this->identicalTo($value));

        $adapter->promise()
            ->always(function () {
            })
            ->then($this->getCallable($mock));

        $adapter->resolve($value);
    }

    public function testAlwaysShouldNotSuppressValueWhenHandlerReturnsANonPromise()
    {
        $adapter = $this->getPromiseTestAdapter();

        $value = new \stdClass();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method(TestCase::MOCK_FUNCTION)
            ->with($this->identicalTo($value));

        $adapter->promise()
            ->always(function () {
                return 1;
            })
            ->then($this->getCallable($mock));

        $adapter->resolve($value);
    }

    public function testAlwaysShouldNotSuppressValueWhenHandlerReturnsAPromise()
    {
        $adapter = $this->getPromiseTestAdapter();

        $value = new \stdClass();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method(TestCase::MOCK_FUNCTION)
            ->with($this->identicalTo($value));

        $adapter->promise()
            ->always(function () {
                return resolve(1);
            })
            ->then($this->getCallable($mock));

        $adapter->resolve($value);
    }

    public function testAlwaysShouldRejectWhenHandlerThrowsForFulfillment()
    {
        $adapter = $this->getPromiseTestAdapter();

        $exception = new \Exception();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method(TestCase::MOCK_FUNCTION)
            ->with($this->identicalTo($exception));

        $adapter->promise()
            ->always(function () use ($exception) {
                throw $exception;
            })
            ->then(null, $this->getCallable($mock));

        $adapter->resolve(1);
    }

    public function testAlwaysShouldRejectWhenHandlerRejectsForFulfillment()
    {
        $adapter = $this->getPromiseTestAdapter();

        $exception = new \Exception();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method(TestCase::MOCK_FUNCTION)
            ->with($this->identicalTo($exception));

        $adapter->promise()
            ->always(function () use ($exception) {
                return reject($exception);
            })
            ->then(null, $this->getCallable($mock));

        $adapter->resolve(1);
    }
}
