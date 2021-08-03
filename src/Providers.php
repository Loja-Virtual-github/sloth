<?php

namespace PabloSanches\Sloth;

use DirectoryIterator;
use PabloSanches\Sloth\InvalidArgumentExceptions;
use SplFileObject;
use SplFileInfo;

class Providers
{
    private $defaultConfigs = array(
        'path'                  => '',
        'path_cache'            => 'cache',
        'minify'                => false,
        'concat'                => false,
        'concat_extension'      => true,
        'prependPrefix'         => false,
        'appendPrefix'          => false,
    );

    public function __construct(array $config)
    {
        $this->config = (object) array_merge(
            $this->defaultConfigs,
            $config
        );
    }

    public function get($key, $default = '')
    {
        if (empty($key)) {
            throw new InvalidArgumentExceptions('Key empty');
        }

        $filepath = $this->getBuildCacheName($key);
        $fileInfo = new SplFileInfo($filepath);
        if ($fileInfo->isFile()) {
            $file = new SplFileObject($filepath, 'r');
            $size = $file->getSize();
            if ($size > 0) {
                $content = $file->fread($size);

                return $content;
            }
        }

        return $default;
    }

    public function set($filename, $content, $getContent = false)
    {
        if (empty($filename)) {
            throw new InvalidArgumentExceptions('Invalid filename');
        }

        if (empty($content)) {
            throw new InvalidArgumentExceptions("Invalid content from {$filename}");
        }

        if ($getContent) {
            $contentStr = '';
            if (is_array($content)) {
                foreach ($content as $contentFile) {
                    $contentStr .= $this->getContent($contentFile);
                }
            } else {
                $contentStr .= $this->getContent($content);
            }
            $content = $contentStr;
        }

        $filepath = $this->getBuildCacheName($filename);
        $content = $this->processContent($content);
        
        if (!is_dir($filepath)) {
            @mkdir($filepath, 0755, true);
        }

        if (file_put_contents($filepath, $content) > 0) {
            return $content;
        }

        return $content;
    }

    public function delete($filename)
    {
        if (empty($filename)) {
            throw new InvalidArgumentExceptions('Invalid filename');
        }

        $filepath = $this->getBuildCacheName($filename);
        $fileInfo = new SplFileInfo($filepath);

        if ($fileInfo->isFile()) {
            return unlink($filepath);
        }

        return false;
    }

    public function has($filename)
    {
        if (empty($filename)) {
            throw new InvalidArgumentExceptions('Filename cannot be empty');
        }

        if (is_array($filename)) {
            $keys = array_keys($filename);
            $filename = $this->buildFileName($keys);
        }
        $cacheName = $this->getBuildCacheName($filename);
        $fileInfo = new SplFileInfo($cacheName);

        return (
            $fileInfo->isFile() &&
            $fileInfo->getSize() > 0
        );
    }

    public function clear()
    {
        $cachePath = $this->getBuildCacheName();
        $dirInfo = new DirectoryIterator($cachePath);
        if (!$dirInfo->isDir()) {
            throw new InvalidArgumentExceptions('Cache directory not founded');
        }

        foreach ($dirInfo as $fileInfo) {
            if ($fileInfo->isDot()) {
                continue;
            }

            if ($fileInfo->isFile()) {
                $pathname = $fileInfo->getPathname();
                if(!unlink($pathname)) {
                    return false;
                }
            }
        }

        return true;
    }

    public function getMultiple(array $keys, $defaults = '')
    {
        if (empty($keys)) {
            throw new InvalidArgumentExceptions('Keys cannot be empty');
        }

        $filename = $this->buildFileName($keys);
        if ($this->has($filename)) {
            $filepath = $this->getBuildCacheName($filename);
            return $this->getContent($filepath);
        }

        $contents = array();
        foreach ($keys as $key) {
            $key = trim($key);

            if (empty($key)) {
                continue;
            }

            $content = $this->get($key, $defaults);
            if (!empty($content)) {
                $contents[$key] = $content;
            }
        }

        if (!empty($contents)) {
            return $contents;
        }

        return $defaults;
    }

    public function setMultiple($values, $getContent = false)
    {
        if (empty($values)) {
            throw new InvalidArgumentExceptions('Values cannot be empty');
        }

        $contentList = array_values($values);
        if ($getContent) {
            $content = $this->getContentFromList($contentList);
        }

        if ($this->config->concat) {
            $filename = $this->buildFileName($contentList);
            return $this->set($filename, $content);
        }

        foreach ($values as $value) {
            if (!$this->set($value, $content)) {
                return false;
            }
        }

        if ($getContent) {
            return $content;
        }

        return true;
    }

    public function deleteMultiple($keys)
    {
        if (empty($keys)) {
            throw new InvalidArgumentExceptions('Keys cannot be empty');
        }

        $filename = $this->buildFileName($keys);
        return $this->delete($filename);
    }

    protected function getContentFromList(array $contentList)
    {
        $content = '';

        if (!empty($contentList)) {
            foreach ($contentList as $contentFile) {
                $content .= $this->getContent($contentFile);
            }
        }

        return $content;
    }

    public function process($file)
    {
        $content = $this->getContentFromList($file);
        return $this->processContent($content);
    }

    protected function getExtension()
    {
        return $this->extension;
    }

    protected function getContent($filepath)
    {
        $content = @file_get_contents($filepath);

        return $content;
    }

    public function getBuildCacheName($endpoint = '')
    {
        $directoryTree = [
            $this->config->path,
        ];

        if (!empty($this->config->path_cache)) {
            $directoryTree[] = $this->config->path_cache;
        }


        $cachePath = implode('/', $directoryTree);
        $cache = new SplFileInfo($cachePath);

        if (is_array($endpoint)) {
            $endpoint = array_keys($endpoint);
            $endpoint = implode('#', $endpoint);
        }

        if ($cache->isDir()) {
            $path = $cachePath . '/' . $endpoint;
            $path = strtr($path, array(
                '//' => '/'
            ));

            return $path;
        }

        if (!empty($this->config->path_cache)) {
            mkdir($cachePath, 0777);
        }

        $path = $cachePath . '/' . $endpoint;
        $path = strtr($path, array(
            '//' => '/'
        ));
        return $path;
    }

    public function buildFileName($keys = null)
    {
        if (!empty($this->config->concat_filename)) {
            $ext = '';

            if ($this->config->concat_extension) {
                $ext = ".{$this->extension}";
            }

            return "{$this->config->concat_filename}{$ext}";
        }

        if (empty($keys)) {
            throw new InvalidArgumentExceptions('Keys cannot be empty');
        }

        if (is_array($keys)) {
            $keys = implode('', $keys);
        }

        $hash = hash('sha1', $keys);
        return "{$hash}.{$this->extension}";
    }
}