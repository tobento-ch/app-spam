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

namespace Tobento\App\Spam\Exception;

use Tobento\App\Spam\DetectorInterface;
use Throwable;

/**
 * SpamDetectedException
 */
class SpamDetectedException extends SpamException
{
    /**
     * Create a new SpamDetectedException.
     *
     * @param DetectorInterface $detector
     * @param string $message
     * @param int $code
     * @param null|Throwable $previous
     * @param null|TokenInterface $token
     */
    public function __construct(
        protected DetectorInterface $detector,
        string $message = '',
        int $code = 0,
        null|Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }
    
    /**
     * Returns the detector.
     *
     * @return DetectorInterface
     */
    public function detector(): DetectorInterface
    {
        return $this->detector;
    }
}