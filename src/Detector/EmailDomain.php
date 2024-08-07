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
 * EmailDomain
 */
class EmailDomain implements DetectorInterface
{
    /**
     * Create a new EmailDomain.
     *
     * @param string $name
     * @param string $inputName
     * @param array<array-key, string> $blacklist Email domains considered as spam.
     * @param array<array-key, string> $whitelist Email domains not considered as spam, exludes from blacklist.
     */
    public function __construct(
        protected string $name,
        protected string $inputName,
        protected array $blacklist = [],
        protected array $whitelist = [],
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
     * Returns the blacklist.
     *
     * @return array<array-key, string>
     */
    public function blacklist(): array
    {
        return $this->blacklist;
    }
    
    /**
     * Returns the whitelist.
     *
     * @return array<array-key, string>
     */
    public function whitelist(): array
    {
        return $this->whitelist;
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
            return;
        }
        
        if (! $this->hasValidEmailDomain($input->get($this->inputName()))) {
            throw new SpamDetectedException(
                detector: $this,
                message: sprintf('input "%s" email domain is blacklisted', $this->inputName())
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
        if (! $this->hasValidEmailDomain($value)) {
            throw new SpamDetectedException(
                detector: $this,
                message: 'input email domain is blacklisted'
            );
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
        return '';
    }
    
    /**
     * Determine if the given value has a valid email domain.
     *
     * @param mixed $value
     * @return bool
     */
    protected function hasValidEmailDomain(mixed $value): bool
    {
        if (! $this->isEmail($value)) {
            return false;
        }
        
        $domain = $this->getDomain($value);
        
        // whitelist:        
        if (in_array($domain, $this->whitelist())) {
            return true;
        }
        
        // blacklist:
        return in_array($domain, $this->blacklist()) ? false : true;
    }
    
    /**
     * Returns true if is email, otherwise false.
     * 
     * @param mixed $value
     * @return bool
     */
    protected function isEmail(mixed $value): bool
    {
        if (!is_string($value)) {
            return false;
        }
        
        return str_contains($value, '@');
    }
    
    /**
     * Returns the email domain.
     * 
     * @param string $email
     * @return string
     */
    protected function getDomain(string $email): string
    {
        return strtolower(substr(strrchr($email, '@'), 1));
    }
}