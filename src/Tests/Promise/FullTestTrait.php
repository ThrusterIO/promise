<?php

namespace Thruster\Component\Promise\Tests\Promise;

/**
 * Trait FullTestTrait
 *
 * @package Thruster\Component\Promise\Tests\Promise
 * @author  Aurimas Niekis <aurimas@niekis.lt>
 */
trait FullTestTrait
{
    use PromisePendingTestTrait,
        PromiseSettledTestTrait,
        PromiseFulfilledTestTrait,
        PromiseRejectedTestTrait,
        ResolveTestTrait,
        RejectTestTrait,
        NotifyTestTrait,
        CancelTestTrait;
}
