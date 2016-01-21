<?php

namespace Thruster\Component\Promise\Tests\Promise;

use Thruster\Component\Promise\Tests\PromiseAdapter\PromiseAdapterInterface;

/**
 * Trait PromisePendingTestTrait
 *
 * @package Thruster\Component\Promise\Tests\Promise
 * @author  Aurimas Niekis <aurimas@niekis.lt>
 */
trait PromisePendingTestTrait
{
    /**
     * @return PromiseAdapterInterface
     */
    abstract public function getPromiseTestAdapter(callable $canceller = null);

    public function testThenShouldReturnAPromiseForPendingPromise()
    {
        $adapter = $this->getPromiseTestAdapter();

        $this->assertInstanceOf('Thruster\\Component\\Promise\\PromiseInterface', $adapter->promise()->then());
    }

    public function testThenShouldReturnAllowNullForPendingPromise()
    {
        $adapter = $this->getPromiseTestAdapter();

        $this->assertInstanceOf(
            'Thruster\\Component\\Promise\\PromiseInterface',
            $adapter->promise()->then(null, null, null)
        );
    }

    public function testCancelShouldReturnNullForPendingPromise()
    {
        $adapter = $this->getPromiseTestAdapter();

        $this->assertNull($adapter->promise()->cancel());
    }

    public function testDoneShouldReturnNullForPendingPromise()
    {
        $adapter = $this->getPromiseTestAdapter();

        $this->assertNull($adapter->promise()->done());
    }

    public function testDoneShouldReturnAllowNullForPendingPromise()
    {
        $adapter = $this->getPromiseTestAdapter();

        $this->assertNull($adapter->promise()->done(null, null, null));
    }

    public function testOtherwiseShouldNotInvokeRejectionHandlerForPendingPromise()
    {
        $adapter = $this->getPromiseTestAdapter();

        $adapter->settle();
        $adapter->promise()->otherwise($this->expectCallableNever());
    }

    public function testAlwaysShouldReturnAPromiseForPendingPromise()
    {
        $adapter = $this->getPromiseTestAdapter();

        $this->assertInstanceOf(
            'Thruster\\Component\\Promise\\PromiseInterface',
            $adapter->promise()->always(function () {
            })
        );
    }
}
