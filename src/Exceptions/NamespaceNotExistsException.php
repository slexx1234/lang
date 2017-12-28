<?php

namespace Slexx\Lang\Exceptions;

use Slexx\Lang\Exception;

class NamespaceNotExistsException extends Exception
{
    /**
     * @var string
     */
    protected $namespace = null;

    /**
     * @param string $namespace
     */
    public function __construct(string $namespace)
    {
        $this->namespace = $namespace;
    }

    /**
     * @return string
     */
    public function getNamespace(): string
    {
        return $this->namespace;
    }
}

