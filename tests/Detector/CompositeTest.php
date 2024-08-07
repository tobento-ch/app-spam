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

class CompositeTest extends TestCase
{
    public function testThatImplementsDetectorInterface()
    {
        $this->assertInstanceof(DetectorInterface::class, new Detector\Composite('name'));
    }
    
    public function testGetterMethods()
    {
        $detector = new Detector\Composite(
            'foo',
            new Detector\NullDetector(name: 'null'),
            new Detector\NullDetector(name: 'null:foo')
        );
        
        $this->assertSame('foo', $detector->name());
    }

    public function testDetectMethodPasses()
    {
        $detector = new Detector\Composite(
            'foo',
            new Detector\NullDetector(name: 'null'),
            new Detector\NullDetector(name: 'null:foo')
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
        $this->expectExceptionMessage('input "message" contains an url');
        
        $detector = new Detector\Composite(
            'foo',
            new Detector\NullDetector(name: 'null'),
            new Detector\WithoutUrl(name: 'wurl', inputNames: ['message'])
        );
        
        $request = (new Psr17Factory())
            ->createServerRequest(method: 'POST', uri: 'uri')
            ->withParsedBody(['message' => 'lorem https://www.example.com']);
        
        $detector->detect(request: $request);
    }
    
    public function testDetectFromValueMethodPasses()
    {
        $detector = new Detector\Composite(
            'foo',
            new Detector\NullDetector(name: 'null'),
            new Detector\WithoutUrl(name: 'wurl', inputNames: ['message'])
        );
        
        $detector->detectFromValue(value: 'lorem');
        
        $this->assertTrue(true);
    }
    
    public function testDetectFromValueMethodFails()
    {
        $this->expectException(SpamDetectedException::class);
        $this->expectExceptionMessage('input contains an url');
        
        $detector = new Detector\Composite(
            'foo',
            new Detector\NullDetector(name: 'null'),
            new Detector\WithoutUrl(name: 'wurl', inputNames: ['message'])
        );
        
        $detector->detectFromValue(value: 'lorem https://www.example.com');
    }
    
    public function testRenderMethod()
    {
        $detector = new Detector\Composite(
            'foo',
            new Detector\NullDetector(name: 'null'),
            new Detector\Honeypot(name: 'foo', inputName: 'hp')
        );
        
        $this->assertStringContainsString(
            '<input type="text" tabindex="-1" name="hp" value="">',
            $detector->render(view: Factory::createView())
        );
    }
}