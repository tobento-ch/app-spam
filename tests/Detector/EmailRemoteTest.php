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

class EmailRemoteTest extends TestCase
{
    public function testThatImplementsDetectorInterface()
    {
        $this->assertInstanceof(DetectorInterface::class, new Detector\EmailRemote(name: 'name', inputName: 'foo'));
    }

    public function testGetterMethods()
    {
        $detector = new Detector\EmailRemote(
            name: 'foo',
            inputName: 'bar',
        );
        
        $this->assertSame('foo', $detector->name());
        $this->assertSame('bar', $detector->inputName());
    }
    
    public function testDetectMethodPasses()
    {
        $detector = new Detector\EmailRemote(
            name: 'foo',
            inputName: 'bar',
            checkDNS: true,
            checkSMTP: false,
            checkMX: true,
            timeoutInSeconds: 5,
        );
        
        $request = (new Psr17Factory())
            ->createServerRequest(method: 'POST', uri: 'uri')
            ->withParsedBody(['bar' => 'info@example.com']);
        
        $detector->detect(request: $request);
        
        $this->assertTrue(true);
    }
    
    public function testDetectMethodPassesIfNotExistAtAll()
    {
        $detector = new Detector\EmailRemote(
            name: 'foo',
            inputName: 'bar',
            checkDNS: true,
            checkSMTP: false,
            checkMX: true,
            timeoutInSeconds: 5,
        );
        
        $request = (new Psr17Factory())
            ->createServerRequest(method: 'POST', uri: 'uri')
            ->withParsedBody([]);
        
        $detector->detect(request: $request);
        
        $this->assertTrue(true);
    }
    
    public function testDetectMethodFails()
    {
        $this->expectException(SpamDetectedException::class);
        $this->expectExceptionMessage('input "bar" has invalid email domain');
        
        $detector = new Detector\EmailRemote(
            name: 'foo',
            inputName: 'bar',
            checkDNS: true,
            checkSMTP: false,
            checkMX: true,
            timeoutInSeconds: 5,
        );
        
        $request = (new Psr17Factory())
            ->createServerRequest(method: 'POST', uri: 'uri')
            ->withParsedBody(['bar' => 'info@1234567abc.com']);
        
        $detector->detect(request: $request);
    }
    
    public function testDetectFromValueMethodPasses()
    {
        $detector = new Detector\EmailRemote(
            name: 'foo',
            inputName: 'bar',
            checkDNS: true,
            checkSMTP: false,
            checkMX: true,
            timeoutInSeconds: 5,
        );
        
        $detector->detectFromValue(value: 'info@example.com');
        
        $this->assertTrue(true);
    }
    
    public function testDetectFromValueMethodFails()
    {
        $this->expectException(SpamDetectedException::class);
        $this->expectExceptionMessage('input has invalid email domain');
        
        $detector = new Detector\EmailRemote(
            name: 'foo',
            inputName: 'bar',
            checkDNS: true,
            checkSMTP: false,
            checkMX: true,
            timeoutInSeconds: 5,
        );
        
        $detector->detectFromValue(value: 'info@1234567abc.com');
    }
    
    public function testRenderMethod()
    {
        $detector = new Detector\EmailRemote(
            name: 'foo',
            inputName: 'email',
            checkDNS: true,
            checkSMTP: true,
            checkMX: true,
            timeoutInSeconds: 5,
        );
        
        $this->assertSame('', $detector->render(view: Factory::createView()));
    }
}