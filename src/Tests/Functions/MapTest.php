<?php

namespace Thruster\Component\Promise\Tests\Functions;

use Thruster\Component\Promise\Tests\TestCase;
use function Thruster\Component\Promise\resolve;
use function Thruster\Component\Promise\reject;
use function Thruster\Component\Promise\map;

/**
 * Class MapTest
 *
 * @package Thruster\Component\Promise\Tests\Functions
 * @author  Aurimas Niekis <aurimas@niekis.lt>
 */
class MapTest extends TestCase
{
    protected function mapper()
    {
        return function ($val) {
            return $val * 2;
        };
    }

    protected function promiseMapper()
    {
        return function ($val) {
            return resolve($val * 2);
        };
    }

    public function testShouldMapInputValuesArray()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method(TestCase::MOCK_FUNCTION)
            ->with($this->identicalTo([2, 4, 6]));

        map(
            [1, 2, 3],
            $this->mapper()
        )->then($this->getCallable($mock));
    }

    public function testShouldMapInputPromisesArray()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method(TestCase::MOCK_FUNCTION)
            ->with($this->identicalTo([2, 4, 6]));

        map(
            [resolve(1), resolve(2), resolve(3)],
            $this->mapper()
        )->then($this->getCallable($mock));
    }

    public function testShouldMapMixedInputArray()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method(TestCase::MOCK_FUNCTION)
            ->with($this->identicalTo([2, 4, 6]));

        map(
            [1, resolve(2), 3],
            $this->mapper()
        )->then($this->getCallable($mock));
    }

    public function testShouldMapInputWhenMapperReturnsAPromise()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method(TestCase::MOCK_FUNCTION)
            ->with($this->identicalTo([2, 4, 6]));

        map(
            [1, 2, 3],
            $this->promiseMapper()
        )->then($this->getCallable($mock));
    }

    public function testShouldAcceptAPromiseForAnArray()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method(TestCase::MOCK_FUNCTION)
            ->with($this->identicalTo([2, 4, 6]));

        map(
            resolve([1, resolve(2), 3]),
            $this->mapper()
        )->then($this->getCallable($mock));
    }

    public function testShouldResolveToEmptyArrayWhenInputPromiseDoesNotResolveToArray()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method(TestCase::MOCK_FUNCTION)
            ->with($this->identicalTo([]));

        map(
            resolve(1),
            $this->mapper()
        )->then($this->getCallable($mock));
    }

    public function testShouldRejectWhenInputContainsRejection()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method(TestCase::MOCK_FUNCTION)
            ->with($this->identicalTo(2));

        map(
            [resolve(1), reject(2), resolve(3)],
            $this->mapper()
        )->then($this->expectCallableNever(), $this->getCallable($mock));
    }
}
