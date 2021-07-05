<?php

namespace PabloSanches\Sloth;

use PabloSanches\Sloth\CacheExceptions;
use Psr\SimpleCache\InvalidArgumentException;

class InvalidArgumentExceptions 
    extends CacheExceptions 
    implements InvalidArgumentException
{
    
}