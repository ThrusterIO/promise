<?php

namespace Thruster\Component\Promise\Tests;

use Thruster\Component\Promise\Deferred;
use Thruster\Component\Promise\FulfilledPromise;
use Thruster\Component\Promise\LazyPromise;
use Thruster\Component\Promise\Tests\Promise\FullTestTrait;
use Thruster\Component\Promise\Tests\PromiseAdapter\CallbackPromiseAdapter;

/**
 * Class LazyPromiseTest
 *
 * @package Thruster\Component\Promise\Tests
 * @author  Aurimas Niekis <aurimas@niekis.lt>
 */
class LazyPromiseTest extends TestCase
{
    use FullTestTrait;

    public function getPromiseTestAdapter(callable $canceller = null)
    {
        $d = new Deferred($canceller);

        $factory = function () use ($d) {
            return $d->promise();
        };

        return new CallbackPromiseAdapter([
            'promise'  => function () use ($factory) {
                return new LazyPromise($factory);
            },
            'resolve' => [$d, 'resolve'],
            'reject'  => [$d, 'reject'],
            'notify'  => [$d, 'notify'],
            'settle'  => [$d, 'resolve'],
        ]);
    }

    public function testShouldNotCallFactoryIfThenIsNotInvoked()
    {
        $factory = $this->createCallableMock();
        $factory
            ->expects($this->never())
            ->method(TestCase::MOCK_FUNCTION);

        new LazyPromise($this->getCallable($factory));
    }

    public function testShouldCallFactoryIfThenIsInvoked()
    {
        $factory = $this->createCallableMock();
        $factory
            ->expects($this->once())
            ->method(TestCase::MOCK_FUNCTION);

        $p = new LazyPromise($this->getCallable($factory));
        $p->then();
    }

    public function testShouldReturnPromiseFromFactory()
    {
        $factory = $this->createCallableMock();
        $factory
            ->expects($this->once())
            ->method(TestCase::MOCK_FUNCTION)
            ->will($this->returnValue(new FulfilledPromise(1)));

        $onFulfilled = $this->createCallableMock();
        $onFulfilled
            ->expects($this->once())
            ->method(TestCase::MOCK_FUNCTION)
            ->with($this->identicalTo(1));

        $p = new LazyPromise($this->getCallable($factory));

        $p->then($this->getCallable($onFulfilled));
    }

    public function testShouldReturnPromiseIfFactoryReturnsNull()
    {
        $factory = $this->createCallableMock();
        $factory
            ->expects($this->once())
            ->method(TestCase::MOCK_FUNCTION)
            ->will($this->returnValue(null));

        $p = new LazyPromise($this->getCallable($factory));
        $this->assertInstanceOf('Thruster\\Component\\Promise\\PromiseInterface', $p->then());
    }

    public function testShouldReturnRejectedPromiseIfFactoryThrowsException()
    {
        $exception = new \Exception();

        $factory = $this->createCallableMock();
        $factory
            ->expects($this->once())
            ->method(TestCase::MOCK_FUNCTION)
            ->will($this->throwException($exception));

        $onRejected = $this->createCallableMock();
        $onRejected
            ->expects($this->once())
            ->method(TestCase::MOCK_FUNCTION)
            ->with($this->identicalTo($exception));

        $p = new LazyPromise($this->getCallable($factory));

        $p->then($this->expectCallableNever(), $this->getCallable($onRejected));
    }
}
