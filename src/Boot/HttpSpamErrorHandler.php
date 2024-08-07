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

namespace Tobento\App\Spam\Boot;

use Tobento\App\Http\Boot\ErrorHandler;
use Tobento\App\Spam\Exception\SpamDetectedException;
use Tobento\Service\Requester\RequesterInterface;
use Psr\Http\Message\ResponseInterface;
use Throwable;

/**
 * HttpSpamErrorHandler boot.
 */
class HttpSpamErrorHandler extends ErrorHandler
{
    public const INFO = [
        'boot' => [
            'Http Spam Error Handler',
        ],
    ];
    
    protected const HANDLER_PRIORITY = 3000;
    
   /**
     * Handle a throwable.
     *
     * @param Throwable $t
     * @return Throwable|ResponseInterface Return throwable if cannot handle, otherwise anything.
     */
    public function handleThrowable(Throwable $t): Throwable|ResponseInterface
    {
        if ($t instanceof SpamDetectedException) {
            $requester = $this->app->get(RequesterInterface::class);
            return $requester->wantsJson()
                ? $this->renderJson(code: 422, message: 'Spam detected')
                : $this->renderView(code: 422, message: 'Spam detected');
        }
        
        return $t;
    }
}