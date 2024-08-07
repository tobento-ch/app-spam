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

class EmailDomainTest extends TestCase
{
    public function testThatImplementsDetectorInterface()
    {
        $this->assertInstanceof(DetectorInterface::class, new Detector\EmailDomain(name: 'name', inputName: 'foo'));
    }

    public function testGetterMethods()
    {
        $detector = new Detector\EmailDomain(
            name: 'foo',
            inputName: 'bar',
            blacklist: ['mail.ru'],
            whitelist: ['gmail.com'],
        );
        
        $this->assertSame('foo', $detector->name());
        $this->assertSame('bar', $detector->inputName());
        $this->assertSame(['mail.ru'], $detector->blacklist());
        $this->assertSame(['gmail.com'], $detector->whitelist());
    }
    
    public function testDetectMethodPassesIfNotInBlacklist()
    {
        $detector = new Detector\EmailDomain(
            name: 'foo',
            inputName: 'bar',
            blacklist: ['mail.ru'],
            whitelist: ['gmail.com'],
        );
        
        $request = (new Psr17Factory())
            ->createServerRequest(method: 'POST', uri: 'uri')
            ->withParsedBody(['bar' => 'info@example.com']);
        
        $detector->detect(request: $request);
        
        $this->assertTrue(true);
    }
    
    public function testDetectMethodPassesIfInBlacklistButWhitelisted()
    {
        $detector = new Detector\EmailDomain(
            name: 'foo',
            inputName: 'bar',
            blacklist: ['mail.ru', 'gmail.com'],
            whitelist: ['gmail.com'],
        );
        
        $request = (new Psr17Factory())
            ->createServerRequest(method: 'POST', uri: 'uri')
            ->withParsedBody(['bar' => 'info@gmail.com']);
        
        $detector->detect(request: $request);
        
        $this->assertTrue(true);
    }
    
    public function testDetectMethodPassesIfNotExistAtAll()
    {
        $detector = new Detector\EmailDomain(
            name: 'foo',
            inputName: 'bar',
            blacklist: ['mail.ru'],
            whitelist: ['gmail.com'],
        );
        
        $request = (new Psr17Factory())
            ->createServerRequest(method: 'POST', uri: 'uri')
            ->withParsedBody([]);
        
        $detector->detect(request: $request);
        
        $this->assertTrue(true);
    }
    
    public function testDetectMethodFailsIfInBlacklist()
    {
        $this->expectException(SpamDetectedException::class);
        $this->expectExceptionMessage('input "bar" email domain is blacklisted');
        
        $detector = new Detector\EmailDomain(
            name: 'foo',
            inputName: 'bar',
            blacklist: ['mail.ru'],
            whitelist: ['gmail.com'],
        );
        
        $request = (new Psr17Factory())
            ->createServerRequest(method: 'POST', uri: 'uri')
            ->withParsedBody(['bar' => 'info@mail.ru']);
        
        $detector->detect(request: $request);
    }
    
    public function testDetectFromValueMethodPassesIfNotInBlacklist()
    {
        $detector = new Detector\EmailDomain(
            name: 'foo',
            inputName: 'bar',
            blacklist: ['mail.ru'],
            whitelist: ['gmail.com'],
        );
        
        $detector->detectFromValue(value: 'info@gmail.com');
        
        $this->assertTrue(true);
    }
    
    public function testDetectFromValueMethodPassesIfInBlacklistButWhitelisted()
    {
        $detector = new Detector\EmailDomain(
            name: 'foo',
            inputName: 'bar',
            blacklist: ['mail.ru', 'gmail.com'],
            whitelist: ['gmail.com'],
        );
        
        $detector->detectFromValue(value: 'info@gmail.com');
        
        $this->assertTrue(true);
    }
    
    public function testDetectFromValueMethodFailsIfInBlacklist()
    {
        $this->expectException(SpamDetectedException::class);
        $this->expectExceptionMessage('input email domain is blacklisted');
        
        $detector = new Detector\EmailDomain(
            name: 'foo',
            inputName: 'bar',
            blacklist: ['mail.ru'],
            whitelist: ['gmail.com'],
        );
        
        $detector->detectFromValue(value: 'mail.ru');
    }
    
    public function testRenderMethod()
    {
        $detector = new Detector\EmailDomain(
            name: 'foo',
            inputName: 'bar',
            blacklist: ['mail.ru'],
            whitelist: ['gmail.com'],
        );
        
        $this->assertSame('', $detector->render(view: Factory::createView()));
    }
}