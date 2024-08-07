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

namespace Tobento\App\Spam\Test;

use PHPUnit\Framework\TestCase;
use Tobento\App\Spam\Detector;
use Tobento\App\Spam\DetectorInterface;
use Tobento\App\Spam\Detectors;
use Tobento\App\Spam\DetectorsInterface;
use Tobento\App\Spam\Factory;
use Tobento\Service\Container\Container;

class DetectorsTest extends TestCase
{
    public function testThatImplementsDetectorsInterfaces()
    {
        $container = new Container();
        $detectors = new Detectors(container: $container, detectors: []);
        
        $this->assertInstanceof(DetectorsInterface::class, $detectors);
    }
    
    public function testAddMethodWithDetector()
    {
        $container = new Container();
        $detectors = new Detectors(container: $container, detectors: []);
        
        $this->assertFalse($detectors->has('foo'));
        $this->assertSame([], $detectors->names());
        
        $detector = new Detector\NullDetector(name: 'foo');
        
        $detectors->add('foo', $detector);
        
        $this->assertTrue($detectors->has('foo'));
        $this->assertSame($detector, $detectors->get('foo'));
        $this->assertSame(['foo'], $detectors->names());
    }
    
    public function testAddMethodWithDetectorFactory()
    {
        $container = new Container();
        $detectors = new Detectors(container: $container, detectors: []);
        
        $this->assertFalse($detectors->has('foo'));
        $this->assertSame([], $detectors->names());
        
        $detector = new Factory\WithoutUrl(inputNames: []);
        
        $detectors->add('foo', $detector);
        
        $this->assertTrue($detectors->has('foo'));
        $this->assertInstanceof(DetectorInterface::class, $detectors->get('foo'));
        $this->assertSame(['foo'], $detectors->names());
    }
    
    public function testConstructDetectors()
    {
        $container = new Container();
        $detectors = new Detectors(container: $container, detectors: [
            'withoutUrl' => new Factory\WithoutUrl(inputNames: []),
            'null' => new Detector\NullDetector(name: 'foo'),
            'emailDomain' => static function (string $name): DetectorInterface {
                return new Detector\EmailDomain(
                    name: $name,
                    inputName: '',
                    blacklist: [],
                    whitelist: [],
                );
            },
        ]);
        
        $this->assertTrue($detectors->has('withoutUrl'));
        $this->assertTrue($detectors->has('null'));
        $this->assertTrue($detectors->has('emailDomain'));
        $this->assertFalse($detectors->has('foo'));
        $this->assertSame(['withoutUrl', 'null', 'emailDomain'], $detectors->names());
        $this->assertInstanceof(DetectorInterface::class, $detectors->get('withoutUrl'));
        $this->assertInstanceof(DetectorInterface::class, $detectors->get('null'));
        $this->assertInstanceof(DetectorInterface::class, $detectors->get('emailDomain'));
    }
    
    public function testGetMethodReturnsFirstDetectorIfNotExist()
    {
        $container = new Container();
        $detectors = new Detectors(container: $container, detectors: [
            'withoutUrl' => new Factory\WithoutUrl(inputNames: []),
        ]);
        
        $this->assertInstanceof(DetectorInterface::class, $detectors->get('foo'));
    }
}