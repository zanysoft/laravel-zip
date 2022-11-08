<?php

namespace ZanySoft\Zip\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \ZanySoft\Zip\Zip open($zip_file)
 * @method static \ZanySoft\Zip\Zip create($zip_file, $overwrite = false)
 * @method static \ZanySoft\Zip\Zip check($zip_file)
 */
class Zip extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'zanysoft.zip';
    }
}
