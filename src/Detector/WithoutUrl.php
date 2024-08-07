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
 * WithoutUrl
 */
class WithoutUrl implements DetectorInterface
{
    /**
     * Create a new WithoutUrl.
     *
     * @param string $name
     * @param array<array-key, string> $inputNames
     */
    public function __construct(
        protected string $name,
        protected array $inputNames,
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
     * Returns the input names.
     *
     * @return array<array-key, string>
     */
    public function inputNames(): array
    {
        return $this->inputNames;
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
        
        foreach($this->inputNames() as $inputName) {
            if ($this->containsUrl($input->get($inputName))) {
                throw new SpamDetectedException(
                    detector: $this,
                    message: sprintf('input "%s" contains an url', $inputName)
                );
            }
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
        if ($this->containsUrl($value)) {
            throw new SpamDetectedException(
                detector: $this,
                message: 'input contains an url'
            );
        }
    }
    
    /**
     * Determine if the given value contains an url.
     *
     * @param mixed $value
     * @return bool
     */
    protected function containsUrl(mixed $value): bool
    {
        if (!is_string($value)) {
            return false;
        }
        
        if (str_contains($value, 'https:') || str_contains($value, 'http:')) {
            return true;
        }
        
        return false;
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