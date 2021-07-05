<?php

namespace PabloSanches\Sloth;

use PabloSanches\Sloth\Providers;
use MatthiasMullie\Minify;

class CSS extends Providers
{
    protected $extension = 'css';

    protected $config;

    public function __construct(array $config)
    {
        parent::__construct($config);
    }

    public static function create(array $config)
    {
        return new CSS($config);
    }

    protected function processContent($content)
    {
        $content = trim($content);

        // Minify all files
        if ($this->config->minify) {
            $this->minify($content);
        }

        return trim($content);
    }

    private function minify(&$content)
    {
        $path = $this->getBuildCacheName();
        $minifier = new Minify\CSS();
        $minifier->add($content);

        $content = $minifier->minify();

        return true;
    }

    private function concat($contents)
    {
        $content = '';


        return $content;
    }
}