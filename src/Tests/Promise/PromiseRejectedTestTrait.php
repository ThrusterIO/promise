<?php

namespace Thruster\Component\Promise\Tests\Promise;

use Thruster\Component\Promise\Deferred;
use Thruster\Component\Promise\Tests\PromiseAdapter\PromiseAdapterInterface;
use Thruster\Component\Promise\Tests\TestCase;
use function Thruster\Component\Promise\resolve;
use function Thruster\Component\Promise\reject;

/**
 * Trait PromiseRejectedTestTrait
 *
 * @package Thruster\Component\Promise\Tests\Promise
 * @author  Aurimas Niekis <aurimas@niekis.lt>
 */
trait PromiseRejectedTestTrait
{
    /**
     * @return PromiseAdapterInterface
     */
    abstract public function getPromiseTestAdapter(callable $canceller = null);

    public function testRejectedPromiseShouldBeImmutable()
    {
        $adapter = $this->getPromiseTestAdapter();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method(TestCase::MOCK_FUNCTION)
            ->with($this->identicalTo(1));

        $adapter->reject(1);
        $adapter->reject(2);

        $adapter->promise()
            ->then(
                $this->expectCallableNever(),
                $this->getCallable($mock)
            );
    }

    public function testRejectedPromiseShouldInvokeNewlyAddedCallback()
    {
        $adapter = $this->getPromiseTestAdapter();

        $adapter->reject(1);

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method(TestCase::MOCK_FUNCTION)
            ->with($this->identicalTo(1));

        $adapter->promise()
            ->then($this->expectCallableNever(), $this->getCallable($mock));
    }

    public function testShouldForwardUndefinedRejectionValue()
    {
        $adapter = $this->getPromiseTestAdapter();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method(TestCase::MOCK_FUNCTION)
            ->with(null);

        $adapter->reject(1);
        $adapter->promise()
            ->then(
                $this->expectCallableNever(),
                function () {
                    // Presence of rejection handler is enough to switch back
                    // to resolve mode, even though it returns undefined.
                    // The ONLY way to propagate a rejection is to re-throw or
                    // return a rejected promise;
                }
            )
            ->then(
                $this->getCallable($mock),
                $this->expectCallableNever()
            );
    }

    public function testShouldSwitchFromErrbacksToCallbacksWhenErrbackDoesNotExplicitlyPropagate()
    {
        $adapter = $this->getPromiseTestAdapter();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method(TestCase::MOCK_FUNCTION)
            ->with($this->identicalTo(2));

        $adapter->reject(1);
        $adapter->promise()
            ->then(
                $this->expectCallableNever(),
                function ($val) {
                    return $val + 1;
                }
            )
            ->then(
                $this->getCallable($mock),
                $this->expectCallableNever()
            );
    }

    public function testShouldSwitchFromErrbacksToCallbacksWhenErrbackReturnsAResolution()
    {
        $adapter = $this->getPromiseTestAdapter();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method(TestCase::MOCK_FUNCTION)
            ->with($this->identicalTo(2));

        $adapter->reject(1);
        $adapter->promise()
            ->then(
                $this->expectCallableNever(),
                function ($val) {
                    return resolve($val + 1);
                }
            )
            ->then(
                $this->getCallable($mock),
                $this->expectCallableNever()
            );
    }

    public function testShouldPropagateRejectionsWhenErrbackThrows()
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

        $adapter->reject(1);
        $adapter->promise()
            ->then(
                $this->expectCallableNever(),
                $this->getCallable($mock)
            )
            ->then(
                $this->expectCallableNever(),
                $this->getCallable($mock2)
            );
    }

    public function testShouldPropagateRejectionsWhenErrbackReturnsARejection()
    {
        $adapter = $this->getPromiseTestAdapter();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method(TestCase::MOCK_FUNCTION)
            ->with($this->identicalTo(2));

        $adapter->reject(1);
        $adapter->promise()
            ->then(
                $this->expectCallableNever(),
                function ($val) {
                    return reject($val + 1);
                }
            )
            ->then(
                $this->expectCallableNever(),
                $this->getCallable($mock)
            );
    }

    public function testDoneShouldInvokeRejectionHandlerForRejectedPromise()
    {
        $adapter = $this->getPromiseTestAdapter();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method(TestCase::MOCK_FUNCTION)
            ->with($this->identicalTo(1));

        $adapter->reject(1);
        $this->assertNull($adapter->promise()->done(null, $this->getCallable($mock)));
    }

    public function testDoneShouldThrowExceptionThrownByRejectionHandlerForRejectedPromise()
    {
        $adapter = $this->getPromiseTestAdapter();

        $this->setExpectedException('\Exception', 'UnhandledRejectionException');

        $adapter->reject(1);
        $this->assertNull($adapter->promise()->done(null, function () {
            throw new \Exception('UnhandledRejectionException');
        }));
    }

    public function testDoneShouldThrowUnhandledRejectionExceptionWhenRejectedWithNonExceptionForRejectedPromise()
    {
        $adapter = $this->getPromiseTestAdapter();

        $this->setExpectedException('Thruster\\Component\\Promise\\Exception\\UnhandledRejectionException');

        $adapter->reject(1);
        $this->assertNull($adapter->promise()->done());
    }

    public function testDoneShouldThrowUnhandledRejectionExceptionWhenRejectionHandlerRejectsForRejectedPromise()
    {
        $adapter = $this->getPromiseTestAdapter();

        $this->setExpectedException('Thruster\\Component\\Promise\\Exception\\UnhandledRejectionException');

        $adapter->reject(1);
        $this->assertNull($adapter->promise()->done(null, function () {
            return reject();
        }));
    }

    public function testDoneShouldThrowRejectionExceptionWhenRejectionHandlerRejectsWithExceptionForRejectedPromise()
    {
        $adapter = $this->getPromiseTestAdapter();

        $this->setExpectedException('\Exception', 'UnhandledRejectionException');

        $adapter->reject(1);
        $this->assertNull($adapter->promise()->done(null, function () {
            return reject(new \Exception('UnhandledRejectionException'));
        }));
    }

    public function testDoneShouldThrowExceptionProvidedAsRejectionValueForRejectedPromise()
    {
        $adapter = $this->getPromiseTestAdapter();

        $this->setExpectedException('\Exception', 'UnhandledRejectionException');

        $adapter->reject(new \Exception('UnhandledRejectionException'));
        $this->assertNull($adapter->promise()->done());
    }

    public function testDoneShouldThrowWithDeepNestingPromiseChainsForRejectedPromise()
    {
        $this->setExpectedException('\Exception', 'UnhandledRejectionException');

        $exception = new \Exception('UnhandledRejectionException');

        $d = new Deferred();
        $d->resolve();

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
    }

    public function testDoneShouldRecoverWhenRejectionHandlerCatchesExceptionForRejectedPromise()
    {
        $adapter = $this->getPromiseTestAdapter();

        $adapter->reject(new \Exception('UnhandledRejectionException'));
        $this->assertNull($adapter->promise()->done(null, function (\Exception $e) {

        }));
    }

    public function testOtherwiseShouldInvokeRejectionHandlerForRejectedPromise()
    {
        $adapter = $this->getPromiseTestAdapter();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method(TestCase::MOCK_FUNCTION)
            ->with($this->identicalTo(1));

        $adapter->reject(1);
        $adapter->promise()->otherwise($this->getCallable($mock));
    }

    public function testOtherwiseShouldInvokeNonTypeHintedRejectionHandlerIfReasonIsAnExceptionForRejectedPromise()
    {
        $adapter = $this->getPromiseTestAdapter();

        $exception = new \Exception();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method(TestCase::MOCK_FUNCTION)
            ->with($this->identicalTo($exception));

        $adapter->reject($exception);
        $adapter->promise()
            ->otherwise(function ($reason) use ($mock) {
                call_user_func($this->getCallable($mock), $reason);
            });
    }

    public function testOtherwiseShouldInvokeRejectionHandlerIfReasonMatchesTypehintForRejectedPromise()
    {
        $adapter = $this->getPromiseTestAdapter();

        $exception = new \InvalidArgumentException();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method(TestCase::MOCK_FUNCTION)
            ->with($this->identicalTo($exception));

        $adapter->reject($exception);
        $adapter->promise()
            ->otherwise(function (\InvalidArgumentException $reason) use ($mock) {
                call_user_func($this->getCallable($mock), $reason);
            });
    }

    public function testOtherwiseShouldNotInvokeRejectionHandlerIfReaonsDoesNotMatchTypehintForRejectedPromise()
    {
        $adapter = $this->getPromiseTestAdapter();

        $exception = new \Exception();

        $mock = $this->expectCallableNever();

        $adapter->reject($exception);
        $adapter->promise()
            ->otherwise(function (\InvalidArgumentException $reason) use ($mock) {
                call_user_func($this->getCallable($mock), $reason);
            });
    }

    public function testAlwaysShouldNotSuppressRejectionForRejectedPromise()
    {
        $adapter = $this->getPromiseTestAdapter();

        $exception = new \Exception();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method(TestCase::MOCK_FUNCTION)
            ->with($this->identicalTo($exception));

        $adapter->reject($exception);
        $adapter->promise()
            ->always(function () {
            })
            ->then(null, $this->getCallable($mock));
    }

    public function testAlwaysShouldNotSuppressRejectionWhenHandlerReturnsANonPromiseForRejectedPromise()
    {
        $adapter = $this->getPromiseTestAdapter();

        $exception = new \Exception();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method(TestCase::MOCK_FUNCTION)
            ->with($this->identicalTo($exception));

        $adapter->reject($exception);
        $adapter->promise()
            ->always(function () {
                return 1;
            })
            ->then(null, $this->getCallable($mock));
    }

    public function testAlwaysShouldNotSuppressRejectionWhenHandlerReturnsAPromiseForRejectedPromise()
    {
        $adapter = $this->getPromiseTestAdapter();

        $exception = new \Exception();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method(TestCase::MOCK_FUNCTION)
            ->with($this->identicalTo($exception));

        $adapter->reject($exception);
        $adapter->promise()
            ->always(function () {
                return resolve(1);
            })
            ->then(null, $this->getCallable($mock));
    }

    public function testAlwaysShouldRejectWhenHandlerThrowsForRejectedPromise()
    {
        $adapter = $this->getPromiseTestAdapter();

        $exception1 = new \Exception();
        $exception2 = new \Exception();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method(TestCase::MOCK_FUNCTION)
            ->with($this->identicalTo($exception2));

        $adapter->reject($exception1);
        $adapter->promise()
            ->always(function () use ($exception2) {
                throw $exception2;
            })
            ->then(null, $this->getCallable($mock));
    }

    public function testAlwaysShouldRejectWhenHandlerRejectsForRejectedPromise()
    {
        $adapter = $this->getPromiseTestAdapter();

        $exception1 = new \Exception();
        $exception2 = new \Exception();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method(TestCase::MOCK_FUNCTION)
            ->with($this->identicalTo($exception2));

        $adapter->reject($exception1);
        $adapter->promise()
            ->always(function () use ($exception2) {
                return reject($exception2);
            })
            ->then(null, $this->getCallable($mock));
    }

    public function testCancelShouldReturnNullForRejectedPromise()
    {
        $adapter = $this->getPromiseTestAdapter();

        $adapter->reject();

        $this->assertNull($adapter->promise()->cancel());
    }

    public function testCancelShouldHaveNoEffectForRejectedPromise()
    {
        $adapter = $this->getPromiseTestAdapter($this->expectCallableNever());

        $adapter->reject();

        $adapter->promise()->cancel();
    }
}
