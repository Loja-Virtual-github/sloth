<?php

namespace PabloSanches\Sloth;

use DirectoryIterator;
use Exception;
use PabloSanches\Sloth\InvalidArgumentExceptions;
use phpDocumentor\Reflection\DocBlock\Tags\Example;
use SplFileObject;
use SplFileInfo;

class Providers
{
    private $defaultConfigs = array(
        'path'          => '',
        'path_cache'    => 'cache',
        'minify'        => false,
        'concat'        => false,
        'prependPrefix' => false,
        'appendPrefix' => false,
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

                return $content; // TODO PROCESS BY ADAPTER
            }
        }

        return $default;
    }

    public function set($filename, $content, $ttl = null)
    {
        if (empty($filename)) {
            throw new InvalidArgumentExceptions('Invalid filename');
        }

        if (empty($content)) {
            throw new InvalidArgumentExceptions("Invalid content from {$filename}");
        }
        
        $filepath = $this->getBuildCacheName($filename);
        $file = new SplFileObject($filepath, 'w');
        $content = $this->processContent($content);
        $bites = $file->fwrite($content);

        return ($bites > 0);
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
        
        if ($getContent) {
            $contentList = array_values($values);
            $content = $this->getContentFromList($contentList);
        }

        if ($this->config->concat) {
            $keys = array_keys($values);
            $filename = $this->buildFileName($keys);
            
            $content = array_values($values);
            $content = implode("\r\n", $content);

            return $this->set($filename, $content);
        }

        foreach ($values as $key => $value) {
            if (!$this->set($key, $value)) {
                return false;
            }
        }

        return true;
    }

    public function deleteMultiple($keys)
    {
        if (empty($keys)) {
            throw new InvalidArgumentExceptions('Keys cannot be empty');
        }

        foreach ($keys as $filename) {
            if (!$this->delete($filename)) {
                return false;
            }
        }

        return true;
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

    protected function getExtension()
    {
        return $this->extension;
    }

    protected function getContent($filepath)
    {
        $content = '';

        $file = new SplFileObject($filepath, 'r');
        $size = $file->getSize();
        if ($size > 0) {
            $content = $file->fread($size);
            $content = $this->processContent($content);
        }

        return $content;
    }

    protected function getBuildCacheName($endpoint = '')
    {
        $directoryTree = [
            $this->config->path,
        ];

        if (!empty($this->config->path_cache)) {
            $directoryTree[] = $this->config->path_cache;
        }


        $cachePath = implode('/', $directoryTree);
        $cache = new SplFileInfo($cachePath);

        if ($cache->isDir()) {
            return $cachePath . '/' . $endpoint;
        }
        
        if (!empty($this->config->path_cache)) {
            mkdir($cachePath, 0755);
        }

        return $cachePath . '/' . $endpoint;
    }

    public function buildFileName($keys = null)
    {
        if (!empty($this->config->concat_filename)) {
            return "{$this->config->concat_filename}.{$this->extension}";
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