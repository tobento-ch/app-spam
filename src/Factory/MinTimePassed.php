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

use Psr\Clock\ClockInterface;
use Psr\Container\ContainerInterface;
use Tobento\App\Spam\Detector;
use Tobento\App\Spam\DetectorInterface;
use Tobento\App\Spam\DetectorFactoryInterface;
use Tobento\Service\Encryption\EncrypterInterface;
use Tobento\Service\Encryption\EncryptersInterface;

/**
 * MinTimePassed
 */
class MinTimePassed implements DetectorFactoryInterface
{
    /**
     * Create a new MinTimePassed.
     *
     * @param string $inputName
     * @param int $milliseconds The minimum milliseconds past as not be detected as spam.
     * @param null|string $encrypterName
     */
    public function __construct(
        protected string $inputName = 'mtp',
        protected int $milliseconds = 1000,
        protected null|string $encrypterName = null,
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
        $encrypter = null;
        
        if ($this->encrypterName) {
            $encrypter = $container->get(EncryptersInterface::class)->get($this->encrypterName);
        }
        
        if (is_null($encrypter)) {
            $encrypter = $container->get(EncrypterInterface::class);
        }
        
        return new Detector\MinTimePassed(
            encrypter: $encrypter,
            clock: $container->get(ClockInterface::class),
            name: $name,
            inputName: $this->inputName,
            milliseconds: $this->milliseconds,
        );
    }
}