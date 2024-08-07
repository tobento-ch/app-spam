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

class NullDetectorTest extends TestCase
{
    public function testThatImplementsDetectorInterface()
    {
        $this->assertInstanceof(DetectorInterface::class, new Detector\NullDetector('name'));
    }

    public function testGetterMethods()
    {
        $detector = new Detector\NullDetector('foo');
        
        $this->assertSame('foo', $detector->name());
    }
    
    public function testDetectMethodAlwaysPassesAsNotSupported()
    {
        $detector = new Detector\NullDetector('foo');
        
        $request = (new Psr17Factory())
            ->createServerRequest(method: 'POST', uri: 'uri')
            ->withParsedBody([]);
        
        $detector->detect(request: $request);
        
        $this->assertTrue(true);
    }
    
    public function testDetectFromValueMethodAlwaysPassesAsNotSupported()
    {
        $detector = new Detector\NullDetector('foo');
        
        $detector->detectFromValue(value: 'lorem');
        
        $this->assertTrue(true);
    }
    
    public function testRenderMethod()
    {
        $detector = new Detector\NullDetector('foo');
        
        $this->assertSame('', $detector->render(view: Factory::createView()));
    }
}