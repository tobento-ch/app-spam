<?php

/**
 * TOBENTO
 *
 * @copyright   Tobias Strub, TOBENTO
 * @license     MIT License, see LICENSE file distributed with this source code.
 * @author      Tobias Strub
 * @link        https://www.tobento.ch
 */

declare(strict_types=1);

namespace Tobento\App\Spam\Test\Detector;

use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;
use Tobento\App\Spam\Detector;
use Tobento\App\Spam\DetectorInterface;
use Tobento\App\Spam\Exception\SpamDetectedException;
use Tobento\App\Spam\Test\Factory;

class WithoutUrlTest extends TestCase
{
    public function testThatImplementsDetectorInterface()
    {
        $this->assertInstanceof(DetectorInterface::class, new Detector\WithoutUrl('name', inputNames: ['message']));
    }

    public function testGetterMethods()
    {
        $detector = new Detector\WithoutUrl('foo', inputNames: ['message']);
        
        $this->assertSame('foo', $detector->name());
        $this->assertSame(['message'], $detector->inputNames());
    }
    
    public function testDetectMethodPassesIfNoUrl()
    {
        $detector = new Detector\WithoutUrl('foo', inputNames: ['message']);
        
        $request = (new Psr17Factory())
            ->createServerRequest(method: 'POST', uri: 'uri')
            ->withParsedBody(['message' => 'lorem']);
        
        $detector->detect(request: $request);
        
        $this->assertTrue(true);
    }
    
    public function testDetectMethodPassesIfNotExist()
    {
        $detector = new Detector\WithoutUrl('foo', inputNames: ['message']);
        
        $request = (new Psr17Factory())
            ->createServerRequest(method: 'POST', uri: 'uri')
            ->withParsedBody([]);
        
        $detector->detect(request: $request);
        
        $this->assertTrue(true);
    }
    
    public function testDetectMethodFailsWithHttps()
    {
        $this->expectException(SpamDetectedException::class);
        $this->expectExceptionMessage('input "message" contains an url');
        
        $detector = new Detector\WithoutUrl('foo', inputNames: ['message']);
        
        $request = (new Psr17Factory())
            ->createServerRequest(method: 'POST', uri: 'uri')
            ->withParsedBody(['message' => 'lorem https://www.example.com']);
        
        $detector->detect(request: $request);
    }
    
    public function testDetectMethodFailsWithHttp()
    {
        $this->expectException(SpamDetectedException::class);
        $this->expectExceptionMessage('input "message" contains an url');
        
        $detector = new Detector\WithoutUrl('foo', inputNames: ['message']);
        
        $request = (new Psr17Factory())
            ->createServerRequest(method: 'POST', uri: 'uri')
            ->withParsedBody(['message' => 'lorem http://www.example.com']);
        
        $detector->detect(request: $request);
    }
    
    public function testDetectMethodWithMultipleInputNamesFailsWithHttps()
    {
        $this->expectException(SpamDetectedException::class);
        $this->expectExceptionMessage('input "msg" contains an url');
        
        $detector = new Detector\WithoutUrl('foo', inputNames: ['message', 'msg']);
        
        $request = (new Psr17Factory())
            ->createServerRequest(method: 'POST', uri: 'uri')
            ->withParsedBody(['message' => 'lorem', 'msg' => 'lorem https://www.example.com']);
        
        $detector->detect(request: $request);
    }
    
    public function testDetectFromValueMethodPasses()
    {
        $detector = new Detector\WithoutUrl('name', inputNames: ['message']);
        
        $detector->detectFromValue(value: 'lorem');
        
        $this->assertTrue(true);
    }
    
    public function testDetectFromValueMethodFailsWithHttps()
    {
        $this->expectException(SpamDetectedException::class);
        $this->expectExceptionMessage('input contains an url');
        
        $detector = new Detector\WithoutUrl('name', inputNames: ['message']);
        
        $detector->detectFromValue(value: 'lorem https://www.example.com');
    }
    
    public function testDetectFromValueMethodFailsWithHttp()
    {
        $this->expectException(SpamDetectedException::class);
        $this->expectExceptionMessage('input contains an url');
        
        $detector = new Detector\WithoutUrl('name', inputNames: ['message']);
        
        $detector->detectFromValue(value: 'lorem http://www.example.com');
    }
    
    public function testRenderMethod()
    {
        $detector = new Detector\WithoutUrl('name', inputNames: ['message']);
        
        $this->assertSame('', $detector->render(view: Factory::createView()));
    }
}