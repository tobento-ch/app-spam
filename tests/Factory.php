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

namespace Tobento\App\Spam\Test;

use PHPUnit\Framework\TestCase;
use Tobento\Service\Encryption\Crypto;
use Tobento\Service\Encryption\EncrypterInterface;
use Tobento\Service\View\ViewInterface;
use Tobento\Service\View\View;
use Tobento\Service\View\PhpRenderer;
use Tobento\Service\View\Data;
use Tobento\Service\View\Assets;
use Tobento\Service\Dir\Dirs;
use Tobento\Service\Dir\Dir;

class Factory
{
    public static function createEncrypter(string $name = 'default'): EncrypterInterface
    {
        return (new Crypto\EncrypterFactory())->createEncrypter(
            name: $name,
            config: [
                'key' => (new Crypto\KeyGenerator())->generateKey(),
            ],
        );
    }
    
    public static function createView(): ViewInterface
    {
        $view = new View(
            new PhpRenderer(
                new Dirs(
                    new Dir(realpath(__DIR__)),
                )
            ),
            new Data(),
            new Assets('public/assets/', 'https://www.example.com/assets/')
        );
        
        /*$view->addMacro('trans', function(string $message) {
            return $message;
        });*/
        
        return $view;
    }
}