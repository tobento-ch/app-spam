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

namespace Tobento\App\Spam\Event;

use Psr\Http\Message\ServerRequestInterface;
use Tobento\App\Spam\Exception\SpamDetectedException;

/**
 * Event after a spam has been detected.
 */
final class SpamDetected
{
    /**
     * Create a new SpamDetected.
     *
     * @param SpamDetectedException $exception
     * @param ServerRequestInterface $request
     */
    public function __construct(
        private SpamDetectedException $exception,
        private ServerRequestInterface $request,
    ) {}

    /**
     * Returns the exception.
     *
     * @return SpamDetectedException
     */
    public function exception(): SpamDetectedException
    {
        return $this->exception;
    }
    
    /**
     * Returns the request.
     *
     * @return ServerRequestInterface
     */
    public function request(): ServerRequestInterface
    {
        return $this->request;
    }
}