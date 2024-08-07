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
use Tobento\Service\Clock\FrozenClock;

class MinTimePassedTest extends TestCase
{
    public function testThatImplementsDetectorInterface()
    {
        $detector = new Detector\MinTimePassed(
            encrypter: Factory::createEncrypter(),
            clock: new FrozenClock(),
            name: 'foo',
            inputName: 'bar',
        );
        
        $this->assertInstanceof(DetectorInterface::class, $detector);
    }

    public function testGetterMethods()
    {
        $detector = new Detector\MinTimePassed(
            encrypter: Factory::createEncrypter(),
            clock: new FrozenClock(),
            name: 'foo',
            inputName: 'bar',
            milliseconds: 2000,
        );
        
        $this->assertSame('foo', $detector->name());
        $this->assertSame('bar', $detector->inputName());
        $this->assertSame(2000, $detector->milliseconds());
    }
    
    public function testDetectMethodPassesIfMinTimePassed()
    {
        $encrypter = Factory::createEncrypter();
        
        $detector = new Detector\MinTimePassed(
            encrypter: $encrypter,
            clock: new FrozenClock(),
            name: 'foo',
            inputName: 'bar',
            milliseconds: 1000,
        );

        $request = (new Psr17Factory())
            ->createServerRequest(method: 'POST', uri: 'uri')
            ->withParsedBody(['bar' => $detector->encryptedTime()]);
        
        $detector = new Detector\MinTimePassed(
            encrypter: $encrypter,
            clock: (new FrozenClock())->modify('+ 1001 milliseconds'),
            name: 'foo',
            inputName: 'bar',
            milliseconds: 1000,
        );
        
        $detector->detect(request: $request);
        
        $this->assertTrue(true);
    }
    
    public function testDetectMethodFailsIfMinTimeNotPassed()
    {
        $this->expectException(SpamDetectedException::class);
        $this->expectExceptionMessage('minimum time not passed for input "bar"');
        
        $encrypter = Factory::createEncrypter();
        
        $detector = new Detector\MinTimePassed(
            encrypter: $encrypter,
            clock: new FrozenClock(),
            name: 'foo',
            inputName: 'bar',
            milliseconds: 1000,
        );

        $request = (new Psr17Factory())
            ->createServerRequest(method: 'POST', uri: 'uri')
            ->withParsedBody(['bar' => $detector->encryptedTime()]);
        
        $detector = new Detector\MinTimePassed(
            encrypter: $encrypter,
            clock: (new FrozenClock())->modify('+ 200 milliseconds'),
            name: 'foo',
            inputName: 'bar',
            milliseconds: 1000,
        );
        
        $detector->detect(request: $request);
    }
    
    public function testDetectMethodFailsIfInvalidTimestamp()
    {
        $this->expectException(SpamDetectedException::class);
        $this->expectExceptionMessage('invalid timestamp for input "bar"');
        
        $encrypter = Factory::createEncrypter();
        
        $detector = new Detector\MinTimePassed(
            encrypter: $encrypter,
            clock: new FrozenClock(),
            name: 'foo',
            inputName: 'bar',
            milliseconds: 1000,
        );

        $request = (new Psr17Factory())
            ->createServerRequest(method: 'POST', uri: 'uri')
            ->withParsedBody(['bar' => 'invalid-time']);
        
        $detector->detect(request: $request);
    }
    
    public function testDetectMethodFailsIfNotExistAtAll()
    {
        $this->expectException(SpamDetectedException::class);
        $this->expectExceptionMessage('input "bar" missing');
        
        $encrypter = Factory::createEncrypter();
        
        $detector = new Detector\MinTimePassed(
            encrypter: $encrypter,
            clock: new FrozenClock(),
            name: 'foo',
            inputName: 'bar',
            milliseconds: 1000,
        );
        
        $request = (new Psr17Factory())
            ->createServerRequest(method: 'POST', uri: 'uri')
            ->withParsedBody([]);
        
        $detector->detect(request: $request);
    }
    
    public function testDetectFromValueMethodAlwaysPassesAsNotSupported()
    {
        $detector = new Detector\MinTimePassed(
            encrypter: Factory::createEncrypter(),
            clock: new FrozenClock(),
            name: 'foo',
            inputName: 'bar',
            milliseconds: 1000,
        );
        
        $detector->detectFromValue(value: 'value');
        
        $this->assertTrue(true);
    }

    public function testRenderMethod()
    {
        $detector = new Detector\MinTimePassed(
            encrypter: Factory::createEncrypter(),
            clock: new FrozenClock(),
            name: 'foo',
            inputName: 'bar',
            milliseconds: 1000,
        );
        
        $this->assertStringContainsString(
            '<input type="hidden" name="bar" value="',
            $detector->render(view: Factory::createView())
        );
    }
}