<?php

namespace Thruster\Component\Promise\Tests\Promise;

use Thruster\Component\Promise\Tests\PromiseAdapter\PromiseAdapterInterface;

/**
 * Trait PromiseSettledTestTrait
 *
 * @package Thruster\Component\Promise\Tests\Promise
 * @author  Aurimas Niekis <aurimas@niekis.lt>
 */
trait PromiseSettledTestTrait
{
    /**
     * @return PromiseAdapterInterface
     */
    abstract public function getPromiseTestAdapter(callable $canceller = null);

    public function testThenShouldReturnAPromiseForSettledPromise()
    {
        $adapter = $this->getPromiseTestAdapter();

        $adapter->settle();
        $this->assertInstanceOf('Thruster\\Component\\Promise\\PromiseInterface', $adapter->promise()->then());
    }

    public function testThenShouldReturnAllowNullForSettledPromise()
    {
        $adapter = $this->getPromiseTestAdapter();

        $adapter->settle();
        $this->assertInstanceOf(
            'Thruster\\Component\\Promise\\PromiseInterface',
            $adapter->promise()->then(null, null, null)
        );
    }

    public function testCancelShouldReturnNullForSettledPromise()
    {
        $adapter = $this->getPromiseTestAdapter();

        $adapter->settle();

        $this->assertNull($adapter->promise()->cancel());
    }

    public function testCancelShouldHaveNoEffectForSettledPromise()
    {
        $adapter = $this->getPromiseTestAdapter($this->expectCallableNever());

        $adapter->settle();

        $adapter->promise()->cancel();
    }

    public function testDoneShouldReturnNullForSettledPromise()
    {
        $adapter = $this->getPromiseTestAdapter();

        $adapter->settle();
        $this->assertNull($adapter->promise()->done(null, function () {
        }));
    }

    public function testDoneShouldReturnAllowNullForSettledPromise()
    {
        $adapter = $this->getPromiseTestAdapter();

        $adapter->settle();
        $this->assertNull($adapter->promise()->done(null, function () {
        }, null));
    }

    public function testProgressShouldNotInvokeProgressHandlerForSettledPromise()
    {
        $adapter = $this->getPromiseTestAdapter();

        $adapter->settle();
        $adapter->promise()->progress($this->expectCallableNever());
        $adapter->notify();
    }

    public function testAlwaysShouldReturnAPromiseForSettledPromise()
    {
        $adapter = $this->getPromiseTestAdapter();

        $adapter->settle();
        $this->assertInstanceOf(
            'Thruster\\Component\\Promise\\PromiseInterface',
            $adapter->promise()->always(function () {
            })
        );
    }
}
