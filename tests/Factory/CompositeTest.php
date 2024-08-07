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

namespace Tobento\App\Spam\Test\Factory;

use PHPUnit\Framework\TestCase;
use Tobento\App\Spam\Detector;
use Tobento\App\Spam\Factory;
use Tobento\App\Spam\DetectorFactoryInterface;
use Tobento\Service\Container\Container;

class CompositeTest extends TestCase
{
    public function testThatImplementsDetectorFactoryInterface()
    {
        $this->assertInstanceof(DetectorFactoryInterface::class, new Factory\Composite());
    }
    
    public function testCreateDetectorWithDetectors()
    {
        $factory = new Factory\Composite(
            new Detector\NullDetector(name: 'null'),
            new Detector\NullDetector(name: 'null:foo')
        );
        
        $detector = $factory->createDetector(name: 'def', container: new Container());
        
        $this->assertInstanceof(Detector\Composite::class, $detector);
        $this->assertSame('def', $detector->name());
    }
    
    public function testCreateDetectorWithFactories()
    {
        $factory = new Factory\Composite(
            new Factory\EmailRemote(inputName: 'email'),
        );
        
        $detector = $factory->createDetector(name: 'def', container: new Container());
        
        $this->assertInstanceof(Detector\Composite::class, $detector);
        $this->assertSame('def', $detector->name());
    }
}