<?php

namespace PabloSanches\Sloth;

use PabloSanches\Sloth\Providers;

class JS extends Providers
{
    protected $extension = 'js';

    protected $config;

    public function __construct(array $config)
    {
        parent::__construct($config);
    }

    public static function create(array $config)
    {
        return new JS($config);
    }

    protected function processContent($content)
    {
        // TODO
        return trim($content);
    }
}