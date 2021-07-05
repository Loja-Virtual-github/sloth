<?php

namespace PabloSanches\Sloth\Tests;

use PHPUnit\Framework\TestCase;

use SplFileInfo;
use SplFileObject;

use PabloSanches\Sloth\Cache;
use PabloSanches\Sloth\CSS;

class CacheCSSTest extends TestCase
{
    private $path;
    private $defaultContent;
    private $config;

    public function setUp()
    {
        $this->path = realpath(__DIR__ . '/assets/css');
        $examplePath = $this->path . '/example.css';
        $file = new SplFileObject($examplePath);
        $this->defaultContent = $file->fread($file->getSize());

        $this->config = array(
            'path' => $this->path
        );
    }

    public function testSet()
    {
        $cache = new Cache(CSS::create($this->config));
        $result = $cache->set('style.css', $this->defaultContent);
        $this->assertTrue($result);
    }

    public function testGet()
    {   
        $cache = new Cache(CSS::create($this->config));
        $content = $cache->get('style.css');
        $this->assertNotEmpty($content);

        $default = '// -- File dont exist';
        $content = $cache->get('index_not_exist.css', $default);
        $this->assertEquals($content, $default);
    }

    public function testDelete()
    {
        $cache = new Cache(CSS::create($this->config));
        $result = $cache->delete('style.css');
        $this->assertTrue($result);

        $fileInfo = new SplFileInfo($this->path . '/style.css');
        $this->assertFalse($fileInfo->isFile());
    }

    public function testHas()
    {        
        $cache = new Cache(CSS::create($this->config));
        $result = $cache->set('style.css', $this->defaultContent);
        $this->assertTrue($result);

        $result = $cache->has('style.css');
        $this->assertTrue($result);

        $result = $cache->delete('style.css');
        $this->assertTrue($result);

        $result = $cache->has('style.css');
        $this->assertFalse($result);
    }

    public function testClear()
    {   
        $cache = new Cache(CSS::create($this->config));
        $result = $cache->clear();
        $this->assertTrue($result);

        $result = $cache->set('style.css', $this->defaultContent);
        $result = $cache->set('style2.css', $this->defaultContent);
        $result = $cache->set('style3.css', $this->defaultContent);
        $this->assertTrue($cache->has('style.css'));
        $this->assertTrue($cache->has('style2.css'));
        $this->assertTrue($cache->has('style3.css'));

        $result = $cache->clear();
        $this->assertTrue($result);

        $this->assertFalse($cache->has('style.css'));
        $this->assertFalse($cache->has('style2.css'));
        $this->assertFalse($cache->has('style3.css'));
    }

    public function testGetMultiple()
    {
        $cache = new Cache(CSS::create($this->config));

        $cache->set('style.css', $this->defaultContent);
        $cache->set('style2.css', $this->defaultContent);
        $cache->set('style3.css', $this->defaultContent);

        $contents = $cache->getMultiple(
            array(
                'style2.css',
                'style3.css'
            ), 
            'Default content'
        );

        $this->assertNotEmpty($contents);
        $this->assertNotEmpty($contents['style2.css']);
        $this->assertNotEmpty($contents['style3.css']);

        $cache->delete('style3.css');
        $contents = $cache->getMultiple(
            array(
                'style2.css',
                'style3.css'
            ), 
            'Default content'
        );
        
        $this->assertNotEquals('Default content', $contents['style2.css']);
        $this->assertEquals('Default content', $contents['style3.css']);
    }

    public function testSetMultiple()
    {
        $cache = new Cache(CSS::create($this->config));
        $result = $cache->setMultiple(array(
            'style.css' => $this->defaultContent,
            'style2.css' => $this->defaultContent,
            'style3.css' => $this->defaultContent,
            'style5.css' => $this->defaultContent,
            'style6.css' => $this->defaultContent
        ), null, true);
        $this->assertTrue($result);

        $cacheGet = new Cache(CSS::create($this->config));
        $contents = $cacheGet->getMultiple(array(
            'style.css',
            'style2.css',
            'style3.css',
            'style4.css'
        ));

        $this->assertNotEmpty($contents);
        $this->assertNotEmpty($contents['style.css']);
        $this->assertNotEmpty($contents['style2.css']);
        $this->assertNotEmpty($contents['style3.css']);
        
        $result = $cache->clear();
        $this->assertTrue($result);

        $contents = $cache->getMultiple(array(
            'style.css',
            'style2.css',
            'style3.css',
            'style5.css'
        ));

        $this->assertEmpty($contents);
    }

    public function testDeleteMultiple()
    {
        $cache = new Cache(CSS::create($this->config));
        $result = $cache->setMultiple(array(
            'style.css' => $this->defaultContent,
            'style2.css' => $this->defaultContent,
            'style3.css' => $this->defaultContent,
            'style5.css' => $this->defaultContent,
            'style6.css' => $this->defaultContent
        ), null, true);
        $this->assertTrue($result);

        $result = $cache->deleteMultiple([
            'style.css',
            'style2.css',
            'style3.css',
            'style5.css'
        ]);
        $this->assertTrue($result);

        $contents = $cache->getMultiple(array(
            'style.css',
            'style2.css',
            'style3.css',
            'style5.css'
        ));

        $this->assertEmpty($contents);
    }

    public function testMinify()
    {
        $cache = new Cache(CSS::create(array(
            'path' => $this->path,
            'minify' => true
        )));

        $result = $cache->set('style.css', $this->defaultContent);
        $this->assertTrue($result);

        $content = $cache->get('style.css');
        $this->assertEquals('body{background:green}body div{width:100px;display:block}', $content);
    }

    public function testConcat()
    {
        $cache = new Cache(CSS::create(array(
            'path' => $this->path,
            'concat' => true,
            'minify' => true,
            'concat_filename' => 'concatenated_file'
        )));

        $set = array(
            'style.css' => $this->defaultContent,
            'style2.css' => $this->defaultContent,
            'style3.css' => $this->defaultContent,
        );
        $result = $cache->setMultiple($set);

        $this->assertTrue($result);

        $keys = array_keys($set);
        $filename = $cache->buildFileName($keys);
        $content = $cache->get($filename);
        
        $test = 'body{background:green}body div{width:100px;display:block}body{background:green}body div{width:100px;display:block}body{background:green}body div{width:100px;display:block}';
        $this->assertEquals($test, $content);
    }
}