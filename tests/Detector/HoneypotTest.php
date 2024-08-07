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

class HoneypotTest extends TestCase
{
    public function testThatImplementsDetectorInterface()
    {
        $this->assertInstanceof(DetectorInterface::class, new Detector\Honeypot(name: 'name', inputName: 'foo'));
    }

    public function testGetterMethods()
    {
        $detector = new Detector\Honeypot(name: 'foo', inputName: 'bar');
        
        $this->assertSame('foo', $detector->name());
        $this->assertSame('bar', $detector->inputName());
    }
    
    public function testDetectMethodPasses()
    {
        $detector = new Detector\Honeypot(name: 'foo', inputName: 'bar');
        
        $request = (new Psr17Factory())
            ->createServerRequest(method: 'POST', uri: 'uri')
            ->withParsedBody(['bar' => '']);
        
        $detector->detect(request: $request);
        
        $this->assertTrue(true);
    }
    
    public function testDetectMethodFailsIfHasValue()
    {
        $this->expectException(SpamDetectedException::class);
        $this->expectExceptionMessage('input "bar" has value');
        
        $detector = new Detector\Honeypot(name: 'foo', inputName: 'bar');
        
        $request = (new Psr17Factory())
            ->createServerRequest(method: 'POST', uri: 'uri')
            ->withParsedBody(['bar' => 'value']);
        
        $detector->detect(request: $request);
    }
    
    public function testDetectMethodFailsIfNotExistAtAll()
    {
        $this->expectException(SpamDetectedException::class);
        $this->expectExceptionMessage('input "bar" missing');
        
        $detector = new Detector\Honeypot(name: 'foo', inputName: 'bar');
        
        $request = (new Psr17Factory())
            ->createServerRequest(method: 'POST', uri: 'uri')
            ->withParsedBody([]);
        
        $detector->detect(request: $request);
    }
    
    public function testDetectFromValueMethodAlwaysPassesAsNotSupported()
    {
        $detector = new Detector\Honeypot(name: 'foo', inputName: 'bar');
        
        $detector->detectFromValue(value: 'value');
        
        $this->assertTrue(true);
    }

    public function testRenderMethod()
    {
        $detector = new Detector\Honeypot(name: 'foo', inputName: 'bar');
        
        $this->assertSame(
            '<div class="display-none"><input type="text" tabindex="-1" name="bar" value=""></div>',
            $detector->render(view: Factory::createView())
        );
    }
}