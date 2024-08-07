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

namespace Tobento\App\Spam\Middleware;

use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Tobento\App\Spam\DetectorsInterface;
use Tobento\App\Spam\DetectorFactoryInterface;
use Tobento\App\Spam\Event;
use Tobento\App\Spam\Exception\SpamDetectedException;

/**
 * ProtectAgainstSpam
 */
class ProtectAgainstSpam implements MiddlewareInterface
{
    /**
     * Create a new ProtectAgainstSpam.
     *
     * @param DetectorsInterface $detectors
     * @param string|DetectorFactoryInterface $detector
     * @param null|EventDispatcherInterface $eventDispatcher
     */
    public function __construct(
        protected DetectorsInterface $detectors,
        protected string|DetectorFactoryInterface $detector = 'default',
        protected null|EventDispatcherInterface $eventDispatcher = null,
    ) {}
    
    /**
     * Process the middleware.
     *
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (is_string($this->detector)) {
            $detector = $this->detectors->get(name: $this->detector);
        } else {
            $name = $this->createDetectorName($request);
            $this->detectors->add(name: $name, detector: $this->detector);
            $detector = $this->detectors->get(name: $name);
        }
        
        try {
            $detector->detect($request);
        } catch (SpamDetectedException $e) {
            $this->eventDispatcher?->dispatch(
                new Event\SpamDetected(exception: $e, request: $request)
            );
            
            throw $e;
        }
        
        return $handler->handle($request);
    }
    
    /**
     * Returns detector name from request.
     *
     * @param ServerRequestInterface $request
     * @return string
     */
    protected function createDetectorName(ServerRequestInterface $request): string
    {
        if (is_string($request->getAttribute('route.name'))) {
            return $request->getAttribute('route.name');
        }
        
        return sha1((string)$request->getUri());
    }
}