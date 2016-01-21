<?php

namespace Thruster\Component\Promise\Tests\Functions;

use Thruster\Component\Promise\Tests\TestCase;
use function Thruster\Component\Promise\resolve;
use function Thruster\Component\Promise\reject;
use function Thruster\Component\Promise\all;

/**
 * Class AllTest
 *
 * @package Thruster\Component\Promise\Tests\Functions
 * @author  Aurimas Niekis <aurimas@niekis.lt>
 */
class AllTest extends TestCase
{
    public function testShouldResolveEmptyInput()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method(TestCase::MOCK_FUNCTION)
            ->with($this->identicalTo([]));

        all([])
            ->then($this->getCallable($mock));
    }

    public function testShouldResolveValuesArray()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method(TestCase::MOCK_FUNCTION)
            ->with($this->identicalTo([1, 2, 3]));

        all([1, 2, 3])
            ->then($this->getCallable($mock));
    }

    public function testShouldResolvePromisesArray()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method(TestCase::MOCK_FUNCTION)
            ->with($this->identicalTo([1, 2, 3]));

        all([resolve(1), resolve(2), resolve(3)])
            ->then($this->getCallable($mock));
    }

    public function testShouldResolveSparseArrayInput()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method(TestCase::MOCK_FUNCTION)
            ->with($this->identicalTo([null, 1, null, 1, 1]));

        all([null, 1, null, 1, 1])
            ->then($this->getCallable($mock));
    }

    public function testShouldRejectIfAnyInputPromiseRejects()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method(TestCase::MOCK_FUNCTION)
            ->with($this->identicalTo(2));

        all([resolve(1), reject(2), resolve(3)])
            ->then($this->expectCallableNever(), $this->getCallable($mock));
    }

    public function testShouldAcceptAPromiseForAnArray()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method(TestCase::MOCK_FUNCTION)
            ->with($this->identicalTo([1, 2, 3]));

        all(resolve([1, 2, 3]))
            ->then($this->getCallable($mock));
    }

    public function testShouldResolveToEmptyArrayWhenInputPromiseDoesNotResolveToArray()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method(TestCase::MOCK_FUNCTION)
            ->with($this->identicalTo([]));

        all(resolve(1))
            ->then($this->getCallable($mock));
    }
}
