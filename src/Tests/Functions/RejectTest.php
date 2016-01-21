<?php

namespace Thruster\Component\Promise\Tests\Functions;

use Thruster\Component\Promise\FulfilledPromise;
use Thruster\Component\Promise\RejectedPromise;
use Thruster\Component\Promise\Tests\TestCase;
use function Thruster\Component\Promise\reject;

/**
 * Class RejectTest
 *
 * @package Thruster\Component\Promise\Tests\Functions
 * @author  Aurimas Niekis <aurimas@niekis.lt>
 */
class RejectTest extends TestCase
{
    public function testShouldRejectAnImmediateValue()
    {
        $expected = 123;

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method(TestCase::MOCK_FUNCTION)
            ->with($this->identicalTo($expected));

        reject($expected)
            ->then(
                $this->expectCallableNever(),
                $this->getCallable($mock)
            );
    }

    public function testShouldRejectAFulfilledPromise()
    {
        $expected = 123;

        $resolved = new FulfilledPromise($expected);

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method(TestCase::MOCK_FUNCTION)
            ->with($this->identicalTo($expected));

        reject($resolved)
            ->then(
                $this->expectCallableNever(),
                $this->getCallable($mock)
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

        reject($resolved)
            ->then(
                $this->expectCallableNever(),
                $this->getCallable($mock)
            );
    }
}
