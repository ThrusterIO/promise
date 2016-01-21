<?php

namespace Thruster\Component\Promise\Tests\Functions;

use Thruster\Component\Promise\Deferred;
use Thruster\Component\Promise\Tests\TestCase;
use function Thruster\Component\Promise\resolve;
use function Thruster\Component\Promise\reject;
use function Thruster\Component\Promise\any;

/**
 * Class AnyTest
 *
 * @package Thruster\Component\Promise\Tests\Functions
 * @author  Aurimas Niekis <aurimas@niekis.lt>
 */
class AnyTest extends TestCase
{
    public function testShouldResolveToNullWithEmptyInputArray()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method(TestCase::MOCK_FUNCTION)
            ->with($this->identicalTo(null));

        any([])
            ->then($this->getCallable($mock));
    }

    public function testShouldResolveWithAnInputValue()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method(TestCase::MOCK_FUNCTION)
            ->with($this->identicalTo(1));

        any([1, 2, 3])
            ->then($this->getCallable($mock));
    }

    public function testShouldResolveWithAPromisedInputValue()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method(TestCase::MOCK_FUNCTION)
            ->with($this->identicalTo(1));

        any([resolve(1), resolve(2), resolve(3)])
            ->then($this->getCallable($mock));
    }

    public function testShouldRejectWithAllRejectedInputValuesIfAllInputsAreRejected()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method(TestCase::MOCK_FUNCTION)
            ->with($this->identicalTo([0 => 1, 1 => 2, 2 => 3]));

        any([reject(1), reject(2), reject(3)])
            ->then($this->expectCallableNever(), $this->getCallable($mock));
    }

    public function testShouldResolveWhenFirstInputPromiseResolves()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method(TestCase::MOCK_FUNCTION)
            ->with($this->identicalTo(1));

        any([resolve(1), reject(2), reject(3)])
            ->then($this->getCallable($mock));
    }

    public function testShouldAcceptAPromiseForAnArray()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method(TestCase::MOCK_FUNCTION)
            ->with($this->identicalTo(1));

        any(resolve([1, 2, 3]))
            ->then($this->getCallable($mock));
    }

    public function testShouldResolveToNullArrayWhenInputPromiseDoesNotResolveToArray()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method(TestCase::MOCK_FUNCTION)
            ->with($this->identicalTo(null));

        any(resolve(1))
            ->then($this->getCallable($mock));
    }

    public function testShouldNotRelyOnArryIndexesWhenUnwrappingToASingleResolutionValue()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method(TestCase::MOCK_FUNCTION)
            ->with($this->identicalTo(2));

        $d1 = new Deferred();
        $d2 = new Deferred();

        any(['abc' => $d1->promise(), 1 => $d2->promise()])
            ->then($this->getCallable($mock));

        $d2->resolve(2);
        $d1->resolve(1);
    }
}
