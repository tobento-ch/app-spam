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
use Tobento\App\Spam\Detectors;
use Tobento\App\Spam\DetectorsInterface;
use Tobento\Service\Container\Container;

class NamedTest extends TestCase
{
    public function testThatImplementsDetectorFactoryInterface()
    {
        $this->assertInstanceof(DetectorFactoryInterface::class, new Factory\Named('foo'));
    }
    
    public function testCreateDetector()
    {
        $factory = new Factory\Named('null');
        $container = new Container();
        $container->set(DetectorsInterface::class, new Detectors($container, [
            'null' => new Detector\NullDetector(name: 'null'),
        ]));
        
        $detector = $factory->createDetector(name: 'def', container: $container);
        
        $this->assertInstanceof(Detector\NullDetector::class, $detector);
        $this->assertSame('null', $detector->name());
    }
}