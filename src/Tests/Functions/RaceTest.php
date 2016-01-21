<?php

namespace Thruster\Component\Promise\Tests\Functions;

use Thruster\Component\Promise\Deferred;
use Thruster\Component\Promise\Tests\TestCase;
use function Thruster\Component\Promise\resolve;
use function Thruster\Component\Promise\reject;
use function Thruster\Component\Promise\race;

/**
 * Class RaceTest
 *
 * @package Thruster\Component\Promise\Tests\Functions
 * @author  Aurimas Niekis <aurimas@niekis.lt>
 */
class RaceTest extends TestCase
{
    public function testShouldResolveEmptyInput()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method(TestCase::MOCK_FUNCTION)
            ->with($this->identicalTo(null));

        race(
            []
        )->then($this->getCallable($mock));
    }

    public function testShouldResolveValuesArray()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method(TestCase::MOCK_FUNCTION)
            ->with($this->identicalTo(1));

        race(
            [1, 2, 3]
        )->then($this->getCallable($mock));
    }

    public function testShouldResolvePromisesArray()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method(TestCase::MOCK_FUNCTION)
            ->with($this->identicalTo(2));

        $d1 = new Deferred();
        $d2 = new Deferred();
        $d3 = new Deferred();

        race(
            [$d1->promise(), $d2->promise(), $d3->promise()]
        )->then($this->getCallable($mock));

        $d2->resolve(2);

        $d1->resolve(1);
        $d3->resolve(3);
    }

    public function testShouldResolveSparseArrayInput()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method(TestCase::MOCK_FUNCTION)
            ->with($this->identicalTo(null));

        race(
            [null, 1, null, 2, 3]
        )->then($this->getCallable($mock));
    }

    public function testShouldRejectIfFirstSettledPromiseRejects()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method(TestCase::MOCK_FUNCTION)
            ->with($this->identicalTo(2));

        $d1 = new Deferred();
        $d2 = new Deferred();
        $d3 = new Deferred();

        race(
            [$d1->promise(), $d2->promise(), $d3->promise()]
        )->then($this->expectCallableNever(), $this->getCallable($mock));

        $d2->reject(2);

        $d1->resolve(1);
        $d3->resolve(3);
    }

    public function testShouldAcceptAPromiseForAnArray()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method(TestCase::MOCK_FUNCTION)
            ->with($this->identicalTo(1));

        race(
            resolve([1, 2, 3])
        )->then($this->getCallable($mock));
    }

    public function testShouldResolveToNullWhenInputPromiseDoesNotResolveToArray()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method(TestCase::MOCK_FUNCTION)
            ->with($this->identicalTo(null));

        race(
            resolve(1)
        )->then($this->getCallable($mock));
    }
}
