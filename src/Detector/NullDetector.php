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

namespace Tobento\App\Spam\Detector;

use Psr\Http\Message\ServerRequestInterface;
use Tobento\App\Spam\DetectorInterface;
use Tobento\App\Spam\Exception\SpamDetectedException;
use Tobento\Service\View\ViewInterface;

/**
 * NullDetector
 */
class NullDetector implements DetectorInterface
{
    /**
     * Create a new NullDetector.
     *
     * @param string $name
     */
    public function __construct(
        protected string $name,
    ) {}
    
    /**
     * Returns the detector name.
     *
     * @return string
     */
    public function name(): string
    {
        return $this->name;
    }
    
    /**
     * Detects spam from request.
     *
     * @param ServerRequestInterface $request
     * @return void
     * @throws SpamDetectedException
     */
    public function detect(ServerRequestInterface $request): void
    {
        //
    }
    
    /**
     * Detects spam from value.
     *
     * @param mixed $value
     * @return void
     * @throws SpamDetectedException
     */
    public function detectFromValue(mixed $value): void
    {
        //
    }
    
    /**
     * Returns the rendered html.
     *
     * @param ViewInterface $view
     * @return string
     */
    public function render(ViewInterface $view): string
    {
        return '';
    }
}