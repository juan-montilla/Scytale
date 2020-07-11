<?php

namespace JuanMontilla\Scytale;

use Illuminate\Support\Facades\Facade;

/**
 * @see \JuanMontilla\Scytale\Skeleton\SkeletonClass
 */
class ScytaleFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'scytale';
    }
}
