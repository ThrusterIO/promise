<?php

namespace Thruster\Component\Promise\Tests\Promise;

use Thruster\Component\Promise\Deferred;
use Thruster\Component\Promise\Tests\PromiseAdapter\PromiseAdapterInterface;
use Thruster\Component\Promise\Tests\TestCase;
use function Thruster\Component\Promise\resolve;
use function Thruster\Component\Promise\reject;

/**
 * Trait RejectTestTrait
 *
 * @package Thruster\Component\Promise\Tests\Promise
 * @author  Aurimas Niekis <aurimas@niekis.lt>
 */
trait RejectTestTrait
{
    /**
     * @return PromiseAdapterInterface
     */
    abstract public function getPromiseTestAdapter(callable $canceller = null);

    public function testRejectShouldRejectWithAnImmediateValue()
    {
        $adapter = $this->getPromiseTestAdapter();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method(TestCase::MOCK_FUNCTION)
            ->with($this->identicalTo(1));

        $adapter->promise()
            ->then($this->expectCallableNever(), $this->getCallable($mock));

        $adapter->reject(1);
    }

    public function testRejectShouldRejectWithFulfilledPromise()
    {
        $adapter = $this->getPromiseTestAdapter();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method(TestCase::MOCK_FUNCTION)
            ->with($this->identicalTo(1));

        $adapter->promise()
            ->then($this->expectCallableNever(), $this->getCallable($mock));

        $adapter->reject(resolve(1));
    }

    public function testRejectShouldRejectWithRejectedPromise()
    {
        $adapter = $this->getPromiseTestAdapter();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method(TestCase::MOCK_FUNCTION)
            ->with($this->identicalTo(1));

        $adapter->promise()
            ->then($this->expectCallableNever(), $this->getCallable($mock));

        $adapter->reject(reject(1));
    }

    public function testRejectShouldForwardReasonWhenCallbackIsNull()
    {
        $adapter = $this->getPromiseTestAdapter();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method(TestCase::MOCK_FUNCTION)
            ->with($this->identicalTo(1));

        $adapter->promise()
            ->then(
                $this->expectCallableNever()
            )
            ->then(
                $this->expectCallableNever(),
                $this->getCallable($mock)
            );

        $adapter->reject(1);
    }

    public function testRejectShouldMakePromiseImmutable()
    {
        $adapter = $this->getPromiseTestAdapter();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method(TestCase::MOCK_FUNCTION)
            ->with($this->identicalTo(1));

        $adapter->promise()
            ->then(null, function ($value) use ($adapter) {
                $adapter->reject(3);

                return reject($value);
            })
            ->then(
                $this->expectCallableNever(),
                $this->getCallable($mock)
            );

        $adapter->reject(1);
        $adapter->reject(2);
    }

    public function testNotifyShouldInvokeOtherwiseHandler()
    {
        $adapter = $this->getPromiseTestAdapter();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method(TestCase::MOCK_FUNCTION)
            ->with($this->identicalTo(1));

        $adapter->promise()
            ->otherwise($this->getCallable($mock));

        $adapter->reject(1);
    }

    public function testDoneShouldInvokeRejectionHandler()
    {
        $adapter = $this->getPromiseTestAdapter();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method(TestCase::MOCK_FUNCTION)
            ->with($this->identicalTo(1));

        $this->assertNull($adapter->promise()->done(null, $this->getCallable($mock)));
        $adapter->reject(1);
    }

    public function testDoneShouldThrowExceptionThrownByRejectionHandler()
    {
        $adapter = $this->getPromiseTestAdapter();

        $this->setExpectedException('\Exception', 'UnhandledRejectionException');

        $this->assertNull($adapter->promise()->done(null, function () {
            throw new \Exception('UnhandledRejectionException');
        }));
        $adapter->reject(1);
    }

    public function testDoneShouldThrowUnhandledRejectionExceptionWhenRejectedWithNonException()
    {
        $adapter = $this->getPromiseTestAdapter();

        $this->setExpectedException('Thruster\\Component\\Promise\\Exception\\UnhandledRejectionException');

        $this->assertNull($adapter->promise()->done());
        $adapter->reject(1);
    }

    public function testDoneShouldThrowUnhandledRejectionExceptionWhenRejectionHandlerRejects()
    {
        $adapter = $this->getPromiseTestAdapter();

        $this->setExpectedException('Thruster\\Component\\Promise\\Exception\\UnhandledRejectionException');

        $this->assertNull($adapter->promise()->done(null, function () {
            return reject();
        }));
        $adapter->reject(1);
    }

    public function testDoneShouldThrowRejectionExceptionWhenRejectionHandlerRejectsWithException()
    {
        $adapter = $this->getPromiseTestAdapter();

        $this->setExpectedException('\Exception', 'UnhandledRejectionException');

        $this->assertNull($adapter->promise()->done(null, function () {
            return reject(new \Exception('UnhandledRejectionException'));
        }));

        $adapter->reject(1);
    }

    public function testDoneShouldThrowUnhandledRejectExcptWhenRejectionHandlerRetunsPendingPromiseWhichRejectsLater()
    {
        $adapter = $this->getPromiseTestAdapter();

        $this->setExpectedException('Thruster\\Component\\Promise\\Exception\\UnhandledRejectionException');

        $d       = new Deferred();
        $promise = $d->promise();

        $this->assertNull($adapter->promise()->done(null, function () use ($promise) {
            return $promise;
        }));

        $adapter->reject(1);
        $d->reject(1);
    }

    public function testDoneShouldThrowExceptionProvidedAsRejectionValue()
    {
        $adapter = $this->getPromiseTestAdapter();

        $this->setExpectedException('\Exception', 'UnhandledRejectionException');

        $this->assertNull($adapter->promise()->done());
        $adapter->reject(new \Exception('UnhandledRejectionException'));
    }

    public function testDoneShouldThrowWithDeepNestingPromiseChains()
    {
        $this->setExpectedException('\Exception', 'UnhandledRejectionException');

        $exception = new \Exception('UnhandledRejectionException');

        $d = new Deferred();

        $result = resolve(resolve($d->promise()->then(function () use ($exception) {
            $d = new Deferred();
            $d->resolve();

            return resolve($d->promise()->then(function () {
            }))->then(
                function () use ($exception) {
                    throw $exception;
                }
            );
        })));

        $result->done();

        $d->resolve();
    }

    public function testDoneShouldRecoverWhenRejectionHandlerCatchesException()
    {
        $adapter = $this->getPromiseTestAdapter();

        $this->assertNull($adapter->promise()->done(null, function (\Exception $e) {

        }));
        $adapter->reject(new \Exception('UnhandledRejectionException'));
    }

    public function testAlwaysShouldNotSuppressRejection()
    {
        $adapter = $this->getPromiseTestAdapter();

        $exception = new \Exception();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method(TestCase::MOCK_FUNCTION)
            ->with($this->identicalTo($exception));

        $adapter->promise()
            ->always(function () {
            })
            ->then(null, $this->getCallable($mock));

        $adapter->reject($exception);
    }

    public function testAlwaysShouldNotSuppressRejectionWhenHandlerReturnsANonPromise()
    {
        $adapter = $this->getPromiseTestAdapter();

        $exception = new \Exception();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method(TestCase::MOCK_FUNCTION)
            ->with($this->identicalTo($exception));

        $adapter->promise()
            ->always(function () {
                return 1;
            })
            ->then(null, $this->getCallable($mock));

        $adapter->reject($exception);
    }

    public function testAlwaysShouldNotSuppressRejectionWhenHandlerReturnsAPromise()
    {
        $adapter = $this->getPromiseTestAdapter();

        $exception = new \Exception();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method(TestCase::MOCK_FUNCTION)
            ->with($this->identicalTo($exception));

        $adapter->promise()
            ->always(function () {
                return resolve(1);
            })
            ->then(null, $this->getCallable($mock));

        $adapter->reject($exception);
    }

    public function testAlwaysShouldRejectWhenHandlerThrowsForRejection()
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

        $adapter->reject($exception);
    }

    public function testAlwaysShouldRejectWhenHandlerRejectsForRejection()
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

        $adapter->reject($exception);
    }
}
