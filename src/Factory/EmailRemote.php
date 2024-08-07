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
 * EmailRemote
 */
class EmailRemote implements DetectorFactoryInterface
{
    /**
     * Create a new EmailRemote.
     *
     * @param string $inputName
     * @param bool $checkDNS
     * @param bool $checkSMTP
     * @param bool $checkMX
     * @param float $timeoutInSeconds
     */
    public function __construct(
        protected string $inputName,
        protected bool $checkDNS = true,
        protected bool $checkSMTP = true,
        protected bool $checkMX = true,
        protected float $timeoutInSeconds = 5,
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
        return new Detector\EmailRemote(
            name: $name,
            inputName: $this->inputName,
            checkDNS: $this->checkDNS,
            checkSMTP: $this->checkSMTP,
            checkMX: $this->checkMX,
            timeoutInSeconds: $this->timeoutInSeconds,
        );
    }
}