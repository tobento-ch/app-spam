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

namespace Tobento\App\Spam\Test\Boot;

use PHPUnit\Framework\TestCase;
use Tobento\App\Spam\Boot\Spam;
use Tobento\App\Spam\DetectorsInterface;
use Tobento\App\AppInterface;
use Tobento\App\AppFactory;
use Tobento\Service\Filesystem\Dir;

class SpamTest extends TestCase
{
    protected function createApp(bool $deleteDir = true): AppInterface
    {
        if ($deleteDir) {
            (new Dir())->delete(__DIR__.'/../app/');
        }
        
        (new Dir())->create(__DIR__.'/../app/');
        
        $app = (new AppFactory())->createApp();
        
        $app->dirs()
            ->dir(realpath(__DIR__.'/../../'), 'root')
            ->dir(realpath(__DIR__.'/../app/'), 'app')
            ->dir($app->dir('app').'config', 'config', group: 'config')
            ->dir($app->dir('root').'vendor', 'vendor')
            // for testing only we add public within app dir.
            ->dir($app->dir('app').'public', 'public');
        
        return $app;
    }
    
    public static function tearDownAfterClass(): void
    {
        (new Dir())->delete(__DIR__.'/../app/');
    }
    
    public function testInterfacesAreAvailable()
    {
        $app = $this->createApp();
        $app->boot(Spam::class);
        $app->booting();
        
        $this->assertInstanceof(DetectorsInterface::class, $app->get(DetectorsInterface::class));
    }
    
    public function testDefaultDetectorsAreAvailable()
    {
        $app = $this->createApp();
        $app->boot(Spam::class);
        $app->booting();
        
        $detectors = $app->get(DetectorsInterface::class);
        
        $this->assertTrue($detectors->has('default'));
        $this->assertTrue($detectors->has('null'));
        $this->assertTrue($detectors->has('email'));
        $this->assertTrue($detectors->has('emailDomain'));
        $this->assertTrue($detectors->has('emailRemote'));
    }
}