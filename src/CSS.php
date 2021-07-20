<?php

namespace PabloSanches\Sloth;

use PabloSanches\Sloth\Providers;
use MatthiasMullie\Minify;

class CSS extends Providers
{
    protected $extension = 'css';

    protected $config;

    protected $prefix = "/*FROM CACHE*/";

    public function __construct(array $config)
    {
        parent::__construct($config);
    }

    public static function create(array $config)
    {
        return new CSS($config);
    }

    public function processContent($content)
    {
        $content = trim($content);

        // Minify all files
        if ($this->config->minify) {
            $this->minify($content);
        }

        if ($this->config->prependPrefix) {
            $content = $this->prefix . "\r\n" . $content;
        } else if ($this->config->appendPrefix) {
            $content = $content . "\r\n" . $this->prefix;
        }

        return trim($content);
    }

    private function minify(&$content)
    {
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