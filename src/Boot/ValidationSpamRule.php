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

use Tobento\App\Boot;
use Tobento\App\Spam\Validation\SpamRule;
use Tobento\Service\Validation\RulesInterface;

/**
 * ValidationSpamRule
 */
class ValidationSpamRule extends Boot
{
    public const INFO = [
        'boot' => [
            'adds spam validation rule',
        ],
    ];

    public const BOOT = [
        \Tobento\App\Spam\Boot\Spam::class,
        \Tobento\App\Validation\Boot\Validator::class,
    ];

    /**
     * Boot application services.
     *
     * @return void
     */
    public function boot(): void
    {
        // using the app on method:
        $this->app->on(RulesInterface::class, static function(RulesInterface $rules) {
            $rules->add(name: 'spam', rule: SpamRule::class);
        });
    }
}