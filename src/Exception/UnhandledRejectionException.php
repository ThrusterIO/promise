<?php

namespace Thruster\Component\Promise\Exception;

/**
 * Class UnhandledRejectionException
 *
 * @package Thruster\Component\Promise\Exception
 * @author  Aurimas Niekis <aurimas@niekis.lt>
 */
class UnhandledRejectionException extends \RuntimeException
{
    private $reason;

    public static function resolve($reason)
    {
        if ($reason instanceof \Exception) {
            return $reason;
        }

        return new static($reason);
    }

    public function __construct($reason)
    {
        $this->reason = $reason;

        $message = sprintf('Unhandled Rejection: %s', json_encode($reason));

        parent::__construct($message, 0);
    }

    public function getReason()
    {
        return $this->reason;
    }
}
