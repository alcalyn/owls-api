<?php

namespace Alcalyn\Owls\Exception;

class CroupierException extends \RuntimeException
{
    /**
     * {@InheritDoc}
     */
    public function __construct($message, $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
