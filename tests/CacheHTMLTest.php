<?php

namespace LojaVirtual\Sloth\Tests;

use PHPUnit\Framework\TestCase;

use SplFileInfo;
use SplFileObject;

use LojaVirtual\Sloth\Cache;
use LojaVirtual\Sloth\HTML;

class CacheHTMLTest extends TestCase
{
    private $path;
    private $defaultContent;
    private $config;

    public function setUp()
    {
        $this->path = realpath(__DIR__ . '/assets/html/');
        $examplePath = $this->path . '/example.html';
        $file = new SplFileObject($examplePath);
        $this->defaultContent = $file->fread($file->getSize());

        $this->config = array(
            'path' => $this->path
        );
    }

    public function testSet()
    {
        $cache = new Cache(HTML::create($this->config));
        $result = $cache->set('index.html', $this->defaultContent);
        $this->assertTrue($result);
    }

    public function testGet()
    {   
        $cache = new Cache(HTML::create($this->config));
        $content = $cache->get('index.html');
        $this->assertNotEmpty($content);

        $default = '<h1>file does not exist</h1>';
        $content = $cache->get('index_not_exist.html', $default);
        $this->assertEquals($content, $default);
    }

    public function testDelete()
    {
        $cache = new Cache(HTML::create($this->config));
        $result = $cache->delete('index.html');
        $this->assertTrue($result);

        $fileInfo = new SplFileInfo($this->path . '/index.html');
        $this->assertFalse($fileInfo->isFile());
    }

    public function testHas()
    {        
        $cache = new Cache(HTML::create($this->config));
        $result = $cache->set('index.html', $this->defaultContent);
        $this->assertTrue($result);

        $result = $cache->has('index.html');
        $this->assertTrue($result);

        $result = $cache->delete('index.html');
        $this->assertTrue($result);

        $result = $cache->has('index.html');
        $this->assertFalse($result);
    }

    public function testClear()
    {   
        $cache = new Cache(HTML::create($this->config));
        $result = $cache->clear();
        $this->assertTrue($result);

        $result = $cache->set('index.html', $this->defaultContent);
        $result = $cache->set('index2.html', $this->defaultContent);
        $result = $cache->set('index3.html', $this->defaultContent);
        $this->assertTrue($cache->has('index.html'));
        $this->assertTrue($cache->has('index2.html'));
        $this->assertTrue($cache->has('index3.html'));

        $result = $cache->clear();
        $this->assertTrue($result);

        $this->assertFalse($cache->has('index.html'));
        $this->assertFalse($cache->has('index2.html'));
        $this->assertFalse($cache->has('index3.html'));
    }

    public function testGetMultiple()
    {
        $cache = new Cache(HTML::create($this->config));

        $cache->set('index.html', $this->defaultContent);
        $cache->set('index2.html', $this->defaultContent);
        $cache->set('index3.html', $this->defaultContent);

        $contents = $cache->getMultiple(
            array(
                'index2.html',
                'index3.html'
            ), 
            'Default content'
        );

        $this->assertNotEmpty($contents);
        $this->assertNotEmpty($contents['index2.html']);
        $this->assertNotEmpty($contents['index3.html']);

        $cache->delete('index3.html');
        $contents = $cache->getMultiple(
            array(
                'index2.html',
                'index3.html'
            ), 
            'Default content'
        );
        
        $this->assertNotEquals('Default content', $contents['index2.html']);
        $this->assertEquals('Default content', $contents['index3.html']);
    }

    public function testSetMultiple()
    {
        $cache = new Cache(HTML::create($this->config));
        $result = $cache->setMultiple(array(
            'index.html' => $this->defaultContent,
            'index2.html' => $this->defaultContent,
            'index3.html' => $this->defaultContent,
            'index5.html' => $this->defaultContent,
            'index6.html' => $this->defaultContent
        ), null, true);
        $this->assertTrue($result);

        $cacheGet = new Cache(HTML::create($this->config));
        $contents = $cacheGet->getMultiple(array(
            'index.html',
            'index2.html',
            'index3.html',
            'index4.html'
        ));

        $this->assertNotEmpty($contents);
        $this->assertNotEmpty($contents['index.html']);
        $this->assertNotEmpty($contents['index2.html']);
        $this->assertNotEmpty($contents['index3.html']);
        
        $result = $cache->clear();
        $this->assertTrue($result);

        $contents = $cache->getMultiple(array(
            'index.html',
            'index2.html',
            'index3.html',
            'index5.html'
        ));

        $this->assertEmpty($contents);
    }

    public function testDeleteMultiple()
    {
        $cache = new Cache(HTML::create($this->config));
        $result = $cache->setMultiple(array(
            'index.html' => $this->defaultContent,
            'index2.html' => $this->defaultContent,
            'index3.html' => $this->defaultContent,
            'index5.html' => $this->defaultContent,
            'index6.html' => $this->defaultContent
        ), null, true);
        $this->assertTrue($result);

        $result = $cache->deleteMultiple([
            'index.html',
            'index2.html',
            'index3.html',
            'index5.html',
            'index6.html',
        ]);
        $this->assertTrue($result);

        $contents = $cache->getMultiple(array(
            'index.html',
            'index2.html',
            'index3.html',
            'index5.html',
            'index6.html',
        ));

        $this->assertEmpty($contents);
    }
}