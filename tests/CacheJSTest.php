<?php

namespace LojaVirtual\Sloth\Tests;

use PHPUnit\Framework\TestCase;

use SplFileInfo;
use SplFileObject;

use LojaVirtual\Sloth\Cache;
use LojaVirtual\Sloth\JS;

class CacheJSTest extends TestCase
{
    private $path;
    private $defaultContent;
    private $config;

    public function setUp()
    {
        $this->path = realpath(__DIR__ . '/assets/js');
        $examplePath = $this->path . '/example.js';
        $file = new SplFileObject($examplePath);
        $this->defaultContent = $file->fread($file->getSize());

        $this->config = array(
            'path' => $this->path
        );
    }

    public function testSet()
    {
        $cache = new Cache(JS::create($this->config));
        $result = $cache->set('script.js', $this->defaultContent);
        $this->assertTrue($result);
    }

    public function testGet()
    {   
        $cache = new Cache(JS::create($this->config));
        $content = $cache->get('script.js');
        $this->assertNotEmpty($content);

        $default = '// -- File dont exist';
        $content = $cache->get('index_not_exist.js', $default);
        $this->assertEquals($content, $default);
    }

    public function testDelete()
    {
        $cache = new Cache(JS::create($this->config));
        $result = $cache->delete('script.js');
        $this->assertTrue($result);

        $fileInfo = new SplFileInfo($this->path . '/script.js');
        $this->assertFalse($fileInfo->isFile());
    }

    public function testHas()
    {        
        $cache = new Cache(JS::create($this->config));
        $result = $cache->set('script.js', $this->defaultContent);
        $this->assertTrue($result);

        $result = $cache->has('script.js');
        $this->assertTrue($result);

        $result = $cache->delete('script.js');
        $this->assertTrue($result);

        $result = $cache->has('script.js');
        $this->assertFalse($result);
    }

    public function testClear()
    {   
        $cache = new Cache(JS::create($this->config));
        $result = $cache->clear();
        $this->assertTrue($result);

        $result = $cache->set('script.js', $this->defaultContent);
        $result = $cache->set('script2.js', $this->defaultContent);
        $result = $cache->set('script3.js', $this->defaultContent);
        $this->assertTrue($cache->has('script.js'));
        $this->assertTrue($cache->has('script2.js'));
        $this->assertTrue($cache->has('script3.js'));

        $result = $cache->clear();
        $this->assertTrue($result);

        $this->assertFalse($cache->has('script.js'));
        $this->assertFalse($cache->has('script2.js'));
        $this->assertFalse($cache->has('script3.js'));
    }

    public function testGetMultiple()
    {
        $cache = new Cache(JS::create($this->config));

        $cache->set('script.js', $this->defaultContent);
        $cache->set('script2.js', $this->defaultContent);
        $cache->set('script3.js', $this->defaultContent);

        $contents = $cache->getMultiple(
            array(
                'script.js',
                'script3.js'
            ), 
            'Default content'
        );

        $this->assertNotEmpty($contents);
        $this->assertNotEmpty($contents['script.js']);
        $this->assertNotEmpty($contents['script3.js']);

        $cache->delete('script3.js');
        $contents = $cache->getMultiple(
            array(
                'script.js',
                'script3.js'
            ), 
            'Default content'
        );
        
        $this->assertNotEquals('Default content', $contents['script.js']);
        $this->assertEquals('Default content', $contents['script3.js']);
    }

    public function testSetMultiple()
    {
        $cache = new Cache(JS::create($this->config));
        $result = $cache->setMultiple(array(
            'script.js' => $this->defaultContent,
            'script2.js' => $this->defaultContent,
            'script3.js' => $this->defaultContent,
            'script5.css' => $this->defaultContent,
            'script6.css' => $this->defaultContent
        ), null, true);
        $this->assertTrue($result);

        $cacheGet = new Cache(JS::create($this->config));
        $contents = $cacheGet->getMultiple(array(
            'script.js',
            'script.js',
            'script3.js',
            'script3.js'
        ));

        $this->assertNotEmpty($contents);
        $this->assertNotEmpty($contents['script.js']);
        $this->assertNotEmpty($contents['script.js']);
        $this->assertNotEmpty($contents['script3.js']);
        
        $result = $cache->clear();
        $this->assertTrue($result);

        $contents = $cache->getMultiple(array(
            'script.js',
            'script.js',
            'script3.js',
            'script5.css'
        ));

        $this->assertEmpty($contents);
    }

    public function testDeleteMultiple()
    {
        $cache = new Cache(JS::create($this->config));
        $result = $cache->setMultiple(array(
            'script.js' => $this->defaultContent,
            'script2.js' => $this->defaultContent,
            'script3.js' => $this->defaultContent,
            'script5.css' => $this->defaultContent
        ), null, true);
        $this->assertTrue($result);

        $result = $cache->deleteMultiple([
            'script.js',
            'script2.js',
            'script3.js',
            'script5.css'
        ]);
        $this->assertTrue($result);

        $contents = $cache->getMultiple(array(
            'script.js',
            'script.js',
            'script3.js',
            'script5.css'
        ));

        $this->assertEmpty($contents);
    }
}