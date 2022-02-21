<?php

namespace LojaVirtual\Sloth;

use LojaVirtual\Sloth\Providers;
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

    public function processContent($content, $forceMinify = false)
    {
        $content = trim($content);

        // Minify all files
        if ($this->config->minify || $forceMinify) {
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

    // protected function getContent($filepath, $fromCache = false)
    // {
    //     if ($fromCache) {
    //         return file_get_contents($filepath);
    //     }

    //     return "@import url('$filepath');" . PHP_EOL;
    // }
}
