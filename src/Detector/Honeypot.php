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
use Tobento\Service\Requester\Requester;
use Tobento\Service\View\ViewInterface;

/**
 * Honeypot
 */
class Honeypot implements DetectorInterface
{
    /**
     * Create a new Honeypot.
     *
     * @param string $name
     * @param string $inputName
     */
    public function __construct(
        protected string $name,
        protected string $inputName,
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
     * Returns the input name.
     *
     * @return string
     */
    public function inputName(): string
    {
        return $this->inputName;
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
        $input = (new Requester($request))->input();
        
        if (! $input->has($this->inputName())) {
            throw new SpamDetectedException(
                detector: $this,
                message: sprintf('input "%s" missing', $this->inputName())
            );
        }
        
        if (!empty($input->get($this->inputName()))) {
            throw new SpamDetectedException(
                detector: $this,
                message: sprintf('input "%s" has value', $this->inputName())
            );
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
        return '<div class="display-none"><input type="text" tabindex="-1" name="'.$view->esc($this->inputName()).'" value=""></div>';
    }
}