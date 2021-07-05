<?php

namespace PabloSanches\Sloth;

use Exception;

use Psr\SimpleCache\CacheException;

class CacheExceptions 
    extends Exception 
    implements CacheException
{
    
}