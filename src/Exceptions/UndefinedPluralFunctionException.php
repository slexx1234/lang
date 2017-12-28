<?php

namespace Slexx\Lang\Exceptions;

use Slexx\Lang\Exception;

class UndefinedPluralFunctionException extends Exception
{
    /**
     * @var string
     */
    protected $locale = null;

    /**
     * @param string $locale
     */
    public function __construct(string $locale)
    {
        $this->locale = $locale;
    }

    /**
     * @return string
     */
    public function getLocale(): string
    {
        return $this->locale;
    }
}

