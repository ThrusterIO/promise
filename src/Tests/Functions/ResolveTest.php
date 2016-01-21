<?php

namespace Thruster\Component\Promise\Tests\Functions;

use Thruster\Component\Promise\Deferred;
use Thruster\Component\Promise\FulfilledPromise;
use Thruster\Component\Promise\RejectedPromise;
use Thruster\Component\Promise\Tests\TestCase;
use function Thruster\Component\Promise\resolve;

/**
 * Class ResolveTest
 *
 * @package Thruster\Component\Promise\Tests\Functions
 * @author  Aurimas Niekis <aurimas@niekis.lt>
 */
class ResolveTest extends TestCase
{
    public function testShouldResolveAnImmediateValue()
    {
        $expected = 123;

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method(TestCase::MOCK_FUNCTION)
            ->with($this->identicalTo($expected));

        resolve($expected)
            ->then(
                $this->getCallable($mock),
                $this->expectCallableNever()
            );
    }

    public function testShouldResolveAFulfilledPromise()
    {
        $expected = 123;

        $resolved = new FulfilledPromise($expected);

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method(TestCase::MOCK_FUNCTION)
            ->with($this->identicalTo($expected));

        resolve($resolved)
            ->then(
                $this->getCallable($mock),
                $this->expectCallableNever()
            );
    }

    public function testShouldRejectARejectedPromise()
    {
        $expected = 123;

        $resolved = new RejectedPromise($expected);

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method(TestCase::MOCK_FUNCTION)
            ->with($this->identicalTo($expected));

        resolve($resolved)
            ->then(
                $this->expectCallableNever(),
                $this->getCallable($mock)
            );
    }

    public function testShouldSupportDeepNestingInPromiseChains()
    {
        $d = new Deferred();
        $d->resolve(false);

        $result = resolve(resolve($d->promise()->then(function ($val) {
            $d = new Deferred();
            $d->resolve($val);

            $identity = function ($val) {
                return $val;
            };

            return resolve($d->promise()->then($identity))->then(
                function ($val) {
                    return !$val;
                }
            );
        })));

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method(TestCase::MOCK_FUNCTION)
            ->with($this->identicalTo(true));

        $result->then($this->getCallable($mock));
    }

    public function testReturnsExtendePromiseForSimplePromise()
    {
        $promise = $this->getMock('Thruster\Component\Promise\PromiseInterface');

        $this->assertInstanceOf('Thruster\Component\Promise\ExtendedPromiseInterface', resolve($promise));
    }
}
