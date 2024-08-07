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

use Psr\Clock\ClockInterface;
use Psr\Http\Message\ServerRequestInterface;
use Tobento\App\Spam\DetectorInterface;
use Tobento\App\Spam\Exception\SpamDetectedException;
use Tobento\Service\Encryption\EncrypterInterface;
use Tobento\Service\Encryption\DecryptException;
use Tobento\Service\Dater\DateFormatter;
use Tobento\Service\Requester\Requester;
use Tobento\Service\View\ViewInterface;

/**
 * MinTimePassed
 */
class MinTimePassed implements DetectorInterface
{
    /**
     * Create a new MinTimePassed.
     *
     * @param EncrypterInterface $encrypter
     * @param ClockInterface $clock
     * @param string $name
     * @param string $inputName
     * @param int $milliseconds The minimum milliseconds past as not be detected as spam.
     */
    public function __construct(
        protected EncrypterInterface $encrypter,
        protected ClockInterface $clock,
        protected string $name,
        protected string $inputName,
        protected int $milliseconds = 1000,
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
     * Returns the milliseconds.
     *
     * @return int
     */
    public function milliseconds(): int
    {
        return $this->milliseconds;
    }
    
    /**
     * Returns the encrypted time.
     *
     * @return string
     */
    public function encryptedTime(): string
    {
        $dateTime = $this->clock->now()->modify('+ '.$this->milliseconds().' milliseconds');
        
        if ($dateTime === false) {
            $dateTime = $this->clock->now();
        }
        
        return $this->encrypter->encrypt($dateTime->getTimestamp());
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
        
        $encryptedTimestamp = $input->get($this->inputName(), '');
        
        try {
            $timestamp = $this->encrypter->decrypt($encryptedTimestamp);
        } catch (DecryptException $e) {
            throw new SpamDetectedException(
                detector: $this,
                message: sprintf('invalid timestamp for input "%s"', $this->inputName()),
            );
        }

        if (! (new DateFormatter())->inPast(date: $timestamp, currentDate: $this->clock->now())) {
            throw new SpamDetectedException(
                detector: $this,
                message: sprintf('minimum time not passed for input "%s"', $this->inputName()),
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
        $time = $this->encryptedTime();
        
        return '<input type="hidden" name="'.$view->esc($this->inputName()).'" value="'.$view->esc($time).'">';
    }
}