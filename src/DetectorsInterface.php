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

/**
 * DetectorsInterface
 */
interface DetectorsInterface
{
    /**
     * Add a detector.
     *
     * @param string $name
     * @param DetectorInterface|DetectorFactoryInterface $detector
     * @return static $this
     */
    public function add(string $name, DetectorInterface|DetectorFactoryInterface $detector): static;
    
    /**
     * Returns true if detector exists, otherwise false.
     *
     * @param string $name
     * @return bool
     */
    public function has(string $name): bool;
    
    /**
     * Returns a detector by name.
     *
     * @param string $name
     * @return DetectorInterface
     */
    public function get(string $name): DetectorInterface;
    
    /**
     * Returns all detector names.
     *
     * @return array<int, string>
     */
    public function names(): array;
}