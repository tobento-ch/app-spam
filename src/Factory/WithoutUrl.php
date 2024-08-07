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
 * WithoutUrl
 */
class WithoutUrl implements DetectorFactoryInterface
{
    /**
     * Create a new WithoutUrl.
     *
     * @param array<array-key, string> $inputNames
     */
    public function __construct(
        protected array $inputNames,
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
        return new Detector\WithoutUrl(
            name: $name,
            inputNames: $this->inputNames
        );
    }
}