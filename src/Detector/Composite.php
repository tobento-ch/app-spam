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
 * Composite
 */
class Composite implements DetectorInterface
{
    /**
     * @var array<array-key, DetectorInterface>
     */
    protected array $detectors = [];
    
    /**
     * Create a new Composite.
     *
     * @param string $name
     * @param DetectorInterface ...$detectors
     */
    public function __construct(
        protected string $name,
        DetectorInterface ...$detectors,
    ) {
        $this->detectors = $detectors;
    }
    
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
        foreach($this->detectors as $detector) {
            $detector->detect($request);
        }
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
        foreach($this->detectors as $detector) {
            $detector->detectFromValue($value);
        }
    }
    
    /**
     * Returns the rendered html.
     *
     * @param ViewInterface $view
     * @return string
     */
    public function render(ViewInterface $view): string
    {
        $html = '';
        
        foreach($this->detectors as $detector) {
            $html .= $detector->render($view);
        }
        
        return $html;
    }
}