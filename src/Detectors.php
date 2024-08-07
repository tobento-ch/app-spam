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

namespace Tobento\App\Spam;

use Psr\Container\ContainerInterface;
use Tobento\Service\Autowire\Autowire;
use LogicException;

/**
 * Detectors
 */
class Detectors implements DetectorsInterface
{
    /**
     * @var Autowire
     */
    protected Autowire $autowire;
    
    /**
     * Create a new Detectors.
     *
     * @param ContainerInterface $container
     * @param array $detectors
     */
    public function __construct(
        ContainerInterface $container,
        protected array $detectors,
    ) {
        $this->autowire = new Autowire($container);
    }
    
    /**
     * Add a detector.
     *
     * @param string $name
     * @param DetectorInterface|DetectorFactoryInterface $detector
     * @return static $this
     */
    public function add(string $name, DetectorInterface|DetectorFactoryInterface $detector): static
    {
        $this->detectors[$name] = $detector;
        return $this;
    }
    
    /**
     * Returns true if detector exists, otherwise false.
     *
     * @param string $name
     * @return bool
     */
    public function has(string $name): bool
    {
        return array_key_exists($name, $this->detectors);
    }
    
    /**
     * Returns a detector by name.
     *
     * @param string $name
     * @return DetectorInterface
     */
    public function get(string $name): DetectorInterface
    {
        if (!isset($this->detectors[$name])) {
            $name = $this->getFirstDetectorName();
        }
        
        if ($this->detectors[$name] instanceof DetectorInterface) {
            return $this->detectors[$name];
        }
        
        // create detector from callable:
        if (is_callable($this->detectors[$name])) {
            return $this->detectors[$name] = $this->autowire->call(
                $this->detectors[$name],
                ['name' => $name]
            );
        }

        // create detector from factory:
        if ($this->detectors[$name] instanceof DetectorFactoryInterface) {
            return $this->detectors[$name] = $this->detectors[$name]->createDetector(
                name: $name,
                container: $this->autowire->container(),
            );
        }
        
        throw new LogicException('Unable to create detector');
    }
    
    /**
     * Returns all detector names.
     *
     * @return array<int, string>
     */
    public function names(): array
    {
        return array_keys($this->detectors);
    }
    
    /**
     * Returns the first detector name.
     *
     * @return string
     */
    protected function getFirstDetectorName(): string
    {
        $name = array_key_first($this->detectors);
        
        if (is_string($name)) {
            return $name;
        }
        
        throw new LogicException('At least one detector is required');
    }
}