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
use Tobento\App\Spam\DetectorsInterface;

/**
 * Named
 */
class Named implements DetectorFactoryInterface
{
    /**
     * Create a new Named.
     *
     * @param string $detector
     */
    public function __construct(
        protected string $detector,
    ) {}

    /**
     * Returns the detector.
     *
     * @param string $name
     * @param ContainerInterface $container
     * @return DetectorInterface
     */
    public function createDetector(string $name, ContainerInterface $container): DetectorInterface
    {
        $detectors = $container->get(DetectorsInterface::class);
        
        return $detectors->get(name: $this->detector);
    }
}