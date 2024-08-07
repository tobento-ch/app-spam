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

namespace Tobento\App\Spam\Factory;

use Psr\Container\ContainerInterface;
use Tobento\App\Spam\Detector;
use Tobento\App\Spam\DetectorInterface;
use Tobento\App\Spam\DetectorFactoryInterface;

/**
 * Composite
 */
class Composite implements DetectorFactoryInterface
{
    /**
     * @var array<array-key, DetectorInterface|DetectorFactoryInterface>
     */
    protected array $detectors = [];
    
    /**
     * Create a new Composite.
     *
     * @param DetectorInterface|DetectorFactoryInterface ...$detectors
     */
    public function __construct(
        DetectorInterface|DetectorFactoryInterface ...$detectors,
    ) {
        $this->detectors = $detectors;
    }
    
    /**
     * Returns the detector.
     *
     * @param string $name
     * @param ContainerInterface $container
     * @return DetectorInterface
     */
    public function createDetector(string $name, ContainerInterface $container): DetectorInterface
    {
        $detectors = [];
        
        foreach ($this->detectors as $detector) {
            if ($detector instanceof DetectorFactoryInterface) {
                $detector = $detector->createDetector($name, $container);
            }
            
            $detectors[] = $detector;
        }
        
        return new Detector\Composite($name, ...$detectors);
    }
}