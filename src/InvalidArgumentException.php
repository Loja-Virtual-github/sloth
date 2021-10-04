<?php

namespace LojaVirtual\Sloth;

use LojaVirtual\Sloth\CacheExceptions;
use Psr\SimpleCache\InvalidArgumentException;

class InvalidArgumentExceptions 
    extends CacheExceptions 
    implements InvalidArgumentException
{
    
}