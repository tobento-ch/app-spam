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

use Psr\Container\ContainerInterface;
use Tobento\App\Boot;
use Tobento\App\Boot\Config;
use Tobento\App\Migration\Boot\Migration;
use Tobento\App\Spam\DetectorInterface;
use Tobento\App\Spam\DetectorFactoryInterface;
use Tobento\App\Spam\DetectorsInterface;
use Tobento\App\Spam\Detectors;
use Tobento\Service\View\ViewInterface;

/**
 * Spam
 */
class Spam extends Boot
{
    public const INFO = [
        'boot' => [
            'installs and loads spam config file',
            'implements spam interfaces',
        ],
    ];

    public const BOOT = [
        Config::class,
        Migration::class,
        HttpSpamErrorHandler::class,
        \Tobento\App\Encryption\Boot\Encryption::class,
    ];

    /**
     * Boot application services.
     *
     * @param Migration $migration
     * @param Config $config
     * @return void
     */
    public function boot(Migration $migration, Config $config): void
    {
        // install migration:
        $migration->install(\Tobento\App\Spam\Migration\Spam::class);
        
        // interfaces:
        $this->app->set(
            DetectorsInterface::class,
            static function(ContainerInterface $container) use ($config): DetectorsInterface {
                $config = $config->load(file: 'spam.php');
                
                return new Detectors(
                    $container,
                    $config['detectors'] ?? []
                );
            }
        );
        
        // view macro:
        $this->app->on(
            ViewInterface::class,
            function(ViewInterface $view) {
                $view->addMacro('spamDetector', [$this, 'detector']);
            }
        );
    }
    
    /**
     * Returns the detector.
     *
     * @param string|DetectorFactoryInterface $detector
     * @return DetectorInterface
     */
    public function detector(string|DetectorFactoryInterface $detector = 'default'): DetectorInterface
    {
        $detectors = $this->app->get(DetectorsInterface::class);
        
        if (is_string($detector)) {
            return $detectors->get(name: $detector);
        }
        
        return $detectors->add(name: 'custom', detector: $detector)->get(name: 'custom');
    }
}