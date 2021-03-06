<?php

namespace Thruster\Component\Promise\Tests;

/**
 * Class TestCase
 *
 * @package Thruster\Component\Promise\Tests
 * @author  Aurimas Niekis <aurimas@niekis.lt>
 */
class TestCase extends \PHPUnit_Framework_TestCase
{
    const MOCK_FUNCTION = 'mockFunction';

    public function expectCallableExactly($amount)
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->exactly($amount))
            ->method(static::MOCK_FUNCTION);

        return $this->getCallable($mock);
    }

    public function expectCallableOnce()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method(static::MOCK_FUNCTION);

        return $this->getCallable($mock);
    }

    public function expectCallableNever()
    {
        $mock = $this->createCallableMock();
        $mock
            ->expects($this->never())
            ->method(static::MOCK_FUNCTION);

        return $this->getCallable($mock);
    }

    public function createCallableMock()
    {
        return $this->getMock(__CLASS__);
    }

    public function getCallable($mock)
    {
        return [$mock, static::MOCK_FUNCTION];
    }

    public function mockFunction()
    {
    }
}
