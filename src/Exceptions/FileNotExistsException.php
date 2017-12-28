<?php

namespace Slexx\Lang\Exceptions;

use Slexx\Lang\Exception;

class FileNotExistsException extends Exception
{
    /**
     * @var string
     */
    protected $path = null;

    /**
     * @param string $path
     */
    public function __construct(string $path)
    {
        $this->path = $path;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }
}

