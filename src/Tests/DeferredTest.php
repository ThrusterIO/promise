<?php

namespace Thruster\Component\Promise\Tests;

use Thruster\Component\Promise\Deferred;
use Thruster\Component\Promise\Tests\Promise\FullTestTrait;
use Thruster\Component\Promise\Tests\PromiseAdapter\CallbackPromiseAdapter;

/**
 * Class DeferredTest
 *
 * @package Thruster\Component\Promise\Tests
 * @author  Aurimas Niekis <aurimas@niekis.lt>
 */
class DeferredTest extends TestCase
{
    use FullTestTrait;

    public function getPromiseTestAdapter(callable $canceller = null)
    {
        $d = new Deferred($canceller);

        return new CallbackPromiseAdapter([
            'promise' => [$d, 'promise'],
            'resolve' => [$d, 'resolve'],
            'reject'  => [$d, 'reject'],
            'notify'  => [$d, 'notify'],
            'settle'  => [$d, 'resolve'],
        ]);
    }

    public function testProgressIsAnAliasForNotify()
    {
        $deferred = new Deferred();

        $sentinel = new \stdClass();

        $mock = $this->createCallableMock();
        $mock
            ->expects($this->once())
            ->method(TestCase::MOCK_FUNCTION)
            ->with($sentinel);

        $deferred->promise()
            ->then($this->expectCallableNever(), $this->expectCallableNever(), $this->getCallable($mock));

        $deferred->notify($sentinel);
    }
}
