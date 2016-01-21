<?php

namespace Thruster\Component\Promise\Tests\PromiseAdapter;

/**
 * Interface PromiseAdapterInterface
 *
 * @package Thruster\Component\Promise\Tests\PromiseAdapter
 * @author  Aurimas Niekis <aurimas@niekis.lt>
 */
interface PromiseAdapterInterface
{
    public function promise();
    public function resolve();
    public function reject();
    public function notify();
    public function settle();
}
