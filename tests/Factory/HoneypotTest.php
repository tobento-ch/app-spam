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

class HoneypotTest extends TestCase
{
    public function testThatImplementsDetectorFactoryInterface()
    {
        $this->assertInstanceof(DetectorFactoryInterface::class, new Factory\Honeypot());
    }
    
    public function testCreateDetector()
    {
        $factory = new Factory\Honeypot(inputName: 'custom_hp');
        
        $detector = $factory->createDetector(name: 'def', container: new Container());
        
        $this->assertInstanceof(Detector\Honeypot::class, $detector);
        $this->assertSame('def', $detector->name());
        $this->assertSame('custom_hp', $detector->inputName());
    }
}