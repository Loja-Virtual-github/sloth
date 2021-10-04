<?php

namespace LojaVirtual\Sloth;

use Exception;

use Psr\SimpleCache\CacheException;

class CacheExceptions 
    extends Exception 
    implements CacheException
{
    
}