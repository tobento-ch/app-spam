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

use Psr\Clock\ClockInterface;
use PHPUnit\Framework\TestCase;
use Tobento\App\Spam\Detector;
use Tobento\App\Spam\Factory;
use Tobento\App\Spam\DetectorFactoryInterface;
use Tobento\App\Spam\Test\Factory as TestFactory;
use Tobento\Service\Container\Container;
use Tobento\Service\Clock\FrozenClock;
use Tobento\Service\Encryption\EncrypterInterface;
use Tobento\Service\Encryption\EncryptersInterface;
use Tobento\Service\Encryption\Encrypters;

class MinTimePassedTest extends TestCase
{
    public function testThatImplementsDetectorFactoryInterface()
    {
        $this->assertInstanceof(DetectorFactoryInterface::class, new Factory\MinTimePassed());
    }

    public function testCreateDetector()
    {
        $factory = new Factory\MinTimePassed(
            inputName: 'custom_mtp',
            milliseconds: 2000,
        );
        
        $container = new Container();
        $container->set(ClockInterface::class, new FrozenClock());
        $container->set(EncrypterInterface::class, TestFactory::createEncrypter('default'));        
        
        $detector = $factory->createDetector(name: 'def', container: $container);
        
        $this->assertInstanceof(Detector\MinTimePassed::class, $detector);
        $this->assertSame('def', $detector->name());
        $this->assertSame('custom_mtp', $detector->inputName());
        $this->assertSame(2000, $detector->milliseconds());
    }
    
    public function testCreateDetectorWithSpecificEncrypter()
    {
        $factory = new Factory\MinTimePassed(
            inputName: 'custom_mtp',
            milliseconds: 2000,
            encrypterName: 'spam',
        );
        
        $container = new Container();
        $container->set(ClockInterface::class, new FrozenClock());
        $container->set(EncryptersInterface::class, function () {
            return new Encrypters(
                TestFactory::createEncrypter('default'),
                TestFactory::createEncrypter('spam'),
            );
        });        
        
        $detector = $factory->createDetector(name: 'def', container: $container);
        
        $this->assertInstanceof(Detector\MinTimePassed::class, $detector);
        $this->assertSame('def', $detector->name());
        $this->assertSame('custom_mtp', $detector->inputName());
        $this->assertSame(2000, $detector->milliseconds());
    }
}