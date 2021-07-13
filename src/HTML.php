<?php

namespace PabloSanches\Sloth;

use PabloSanches\Sloth\Providers;
use PabloSanches\Sloth\InvalidArgumentExceptions;

use SplFileObject;
use Phar;
use PharFileInfo;
use SplFileInfo;

class HTML extends Providers
{
    const CACHE_PREFIX = '<!-- FROM CACHE -->';
    protected $extension = 'html';

    protected $config;

    public function __construct(array $config)
    {
        parent::__construct($config);
    }

    public static function create(array $config)
    {
        return new HTML($config);
    }

    protected function processContent($content)
    {
        $content = self::CACHE_PREFIX . "\r\n" . $content;
        
        return trim($content);
    }
}