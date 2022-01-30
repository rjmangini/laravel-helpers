<?php

namespace rjmangini\Helpers\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \App\Helpers\Helper
 */
class Helper extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'helper';
    }
}
