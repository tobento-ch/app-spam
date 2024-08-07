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
 * EmailRemote
 */
class EmailRemote implements DetectorInterface
{
    /**
     * Create a new EmailRemote.
     *
     * @param string $name
     * @param string $inputName
     * @param bool $checkDNS
     * @param bool $checkSMTP
     * @param bool $checkMX
     * @param float $timeoutInSeconds
     */
    public function __construct(
        protected string $name,
        protected string $inputName,
        protected bool $checkDNS = true,
        protected bool $checkSMTP = true,
        protected bool $checkMX = true,
        protected float $timeoutInSeconds = 5,
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
            return;
        }
        
        if (! $this->hasValidEmailDomain($input->get($this->inputName()))) {
            throw new SpamDetectedException(
                detector: $this,
                message: sprintf('input "%s" has invalid email domain', $this->inputName())
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
                message: 'input has invalid email domain'
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
        
        try {
            if ($this->checkDNS && !$this->isDNSValid($domain)) {
                return false;
            }

            if ($this->checkSMTP && !$this->isSMTPValid($domain)) {
                return false;
            }

            if ($this->checkMX && !$this->isMXValid($domain)) {
                return false;
            }

            return true;
        } catch (\Exception $e) {
            throw new SpamDetectedException($this, $e->getMessage(), (int)$e->getCode(), $e);
        }
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
    
    /**
     * Check if the DNS record is valid for the email domain.
     *
     * @param string $domain
     * @return bool
     */
    protected function isDNSValid(string $domain): bool
    {
        return checkdnsrr($domain, 'A');
    }

    /**
     * Check if the SMTP server is valid for the email domain.
     *
     * @param string $domain
     * @return bool
     */
    protected function isSMTPValid(string $domain): bool
    {
        $smtp = fsockopen($domain, 25, $errno, $errstr, $this->timeoutInSeconds);
        
        if ($smtp) {
            fclose($smtp);
            return true;
        }
        
        return false;
    }

    /**
     * Check if the MX record is valid for the email domain.
     *
     * @param string $domain
     * @return bool
     */
    protected function isMXValid(string $domain): bool
    {
        return getmxrr($domain, $mxhosts);
    }
}