<?php

namespace Thruster\Component\Promise\Tests\Functions;

use Thruster\Component\Promise\Tests\TestCase;
use function Thruster\Component\Promise\resolve;
use function Thruster\Component\Promise\reject;
use function Thruster\Component\Promise\some;

/**
 * Class SomeTest
 *
 * @package Thruster\Component\Promise\Tests\Functions
 * @author  Aurimas Niekis <aurimas@niekis.lt>
 */
class SomeTest extends TestCase
{
    public function testShouldResolveEmptyInput()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method(TestCase::MOCK_FUNCTION)
            ->with($this->identicalTo([]));

        some(
            [],
            1
        )->then($this->getCallable($mock));
    }

    public function testShouldResolveValuesArray()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method(TestCase::MOCK_FUNCTION)
            ->with($this->identicalTo([1, 2]));

        some(
            [1, 2, 3],
            2
        )->then($this->getCallable($mock));
    }

    public function testShouldResolvePromisesArray()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method(TestCase::MOCK_FUNCTION)
            ->with($this->identicalTo([1, 2]));

        some(
            [resolve(1), resolve(2), resolve(3)],
            2
        )->then($this->getCallable($mock));
    }

    public function testShouldResolveSparseArrayInput()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method(TestCase::MOCK_FUNCTION)
            ->with($this->identicalTo([null, 1]));

        some(
            [null, 1, null, 2, 3],
            2
        )->then($this->getCallable($mock));
    }

    public function testShouldRejectIfAnyInputPromiseRejectsBeforeDesiredNumberOfInputsAreResolved()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method(TestCase::MOCK_FUNCTION)
            ->with($this->identicalTo([1 => 2, 2 => 3]));

        some(
            [resolve(1), reject(2), reject(3)],
            2
        )->then($this->expectCallableNever(), $this->getCallable($mock));
    }

    public function testShouldAcceptAPromiseForAnArray()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method(TestCase::MOCK_FUNCTION)
            ->with($this->identicalTo([1, 2]));

        some(
            resolve([1, 2, 3]),
            2
        )->then($this->getCallable($mock));
    }

    public function testShouldResolveWithEmptyArrayIfHowManyIsLessThanOne()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method(TestCase::MOCK_FUNCTION)
            ->with($this->identicalTo([]));

        some(
            [1],
            0
        )->then($this->getCallable($mock));
    }

    public function testShouldResolveToEmptyArrayWhenInputPromiseDoesNotResolveToArray()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method(TestCase::MOCK_FUNCTION)
            ->with($this->identicalTo([]));

        some(
            resolve(1),
            1
        )->then($this->getCallable($mock));
    }
}
