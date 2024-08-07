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

namespace Tobento\App\App\Test\Feature;

use PHPUnit\Framework\TestCase;
use Psr\Clock\ClockInterface;
use Tobento\App\AppInterface;
use Tobento\App\Spam\Detector;
use Tobento\App\Spam\Event;
use Tobento\App\Spam\Factory;
use Tobento\App\Spam\Middleware\ProtectAgainstSpam;
use Tobento\Service\Clock\FrozenClock;
use Tobento\Service\Responser\ResponserInterface;
use Tobento\Service\Routing\RouterInterface;
use Tobento\Service\View\ViewInterface;

class SpamFormsTest extends \Tobento\App\Testing\TestCase
{
    public function createApp(): AppInterface
    {
        $app = $this->createTmpApp(rootDir: __DIR__.'/../..');
        $app->boot(\Tobento\App\Http\Boot\RequesterResponser::class);
        $app->boot(\Tobento\App\Http\Boot\Routing::class);
        $app->boot(\Tobento\App\View\Boot\View::class);
        $app->boot(\Tobento\App\View\Boot\Form::class);
        $app->boot(\Tobento\App\Event\Boot\Event::class);
        $app->boot(\Tobento\App\Spam\Boot\Spam::class);
        
        $app->on(RouterInterface::class, static function(RouterInterface $router): void {
            $router->get('register/default', function (ResponserInterface $responser, ViewInterface $view) {
                return $responser->html(
                    html: $view->spamDetector()->render($view),
                    code: 200,
                );
            })->name('register.default');
            
            $router->get('register', function (ResponserInterface $responser, ViewInterface $view) {
                return $responser->html(
                    html: $view->spamDetector('register')->render($view),
                    code: 200,
                );
            })->name('register');
        });
        
        return $app;
    }

    public function testMiddlewareWithDefaultDetectorPasses()
    {
        $events = $this->fakeEvents();
        $http = $this->fakeHttp();
        $http->request(method: 'GET', uri: 'register/default');
        $response = $http->response();
        $http->request(method: 'POST', uri: 'register', body: [
            'mtp' => $response->crawl()->filter('input[name="mtp"]')->attr('value'),
            'hp' => '',
        ]);
        
        $this->getApp()->on(ClockInterface::class, function($clock) {
            return new FrozenClock($clock->now()->modify('+1001 milliseconds'));
        });
        
        $app = $this->bootingApp();
        $app->get(RouterInterface::class)->post('register', function (ResponserInterface $responser) {
            return $responser->write(data: '', code: 200);
        })->middleware(ProtectAgainstSpam::class);
        
        $http->response()->assertStatus(200);
        
        $this->fakeEvents()->assertNotDispatched(Event\SpamDetected::class);
    }
    
    public function testMiddlewareWithDefaultDetectorFailsIfTimeNotPassed()
    {
        $events = $this->fakeEvents();
        $http = $this->fakeHttp();
        $http->request(method: 'GET', uri: 'register/default');
        $response = $http->response();
        $http->request(method: 'POST', uri: 'register', body: [
            'mtp' => $response->crawl()->filter('input[name="mtp"]')->attr('value'),
            'hp' => '',
        ]);
        
        $this->getApp()->on(ClockInterface::class, function($clock) {
            return new FrozenClock($clock->now()->modify('+100 milliseconds'));
        });
        
        $app = $this->bootingApp();
        $app->get(RouterInterface::class)->post('register', function (ResponserInterface $responser) {
            return $responser->write(data: '', code: 200);
        })->middleware(ProtectAgainstSpam::class);
        
        $http->response()->assertStatus(422);
        
        $this->fakeEvents()->assertDispatched(Event\SpamDetected::class);
    }
    
    public function testMiddlewareWithDefaultDetectorFailsIfHoneypotHasValue()
    {
        $http = $this->fakeHttp();
        $http->request(method: 'GET', uri: 'register/default');
        $response = $http->response();
        $http->request(method: 'POST', uri: 'register', body: [
            'mtp' => $response->crawl()->filter('input[name="mtp"]')->attr('value'),
            'hp' => 'value',
        ]);
        
        $this->getApp()->on(ClockInterface::class, function($clock) {
            return new FrozenClock($clock->now()->modify('+1001 milliseconds'));
        });
        
        $app = $this->bootingApp();
        $app->get(RouterInterface::class)->post('register', function (ResponserInterface $responser) {
            return $responser->write(data: '', code: 200);
        })->middleware(ProtectAgainstSpam::class);
        
        $http->response()->assertStatus(422);
    }
    
    public function testMiddlewareWithSpecificDetectorFallsbackToDefaultIfNotExist()
    {
        $http = $this->fakeHttp();
        $http->request(method: 'GET', uri: 'register');
        $response = $http->response();
        $http->request(method: 'POST', uri: 'register', body: [
            'mtp' => $response->crawl()->filter('input[name="mtp"]')->attr('value'),
            'hp' => '',
        ]);
        
        $this->getApp()->on(ClockInterface::class, function($clock) {
            return new FrozenClock($clock->now()->modify('+100 milliseconds'));
        });
        
        $app = $this->bootingApp();
        $app->get(RouterInterface::class)->post('register', function (ResponserInterface $responser) {
            return $responser->write(data: '', code: 200);
        })->middleware([
              ProtectAgainstSpam::class,
              'detector' => 'register',
          ]);
        
        $http->response()->assertStatus(422);
    }
    
    public function testMiddlewareWithSpecificDetectorPasses()
    {
        $config = $this->fakeConfig();
        $config->with('spam.detectors', [
            'register' => new Factory\Composite(
                new Factory\Honeypot(inputName: 'custom_hp'),
                new Factory\MinTimePassed(inputName: 'custom_mtp', milliseconds: 2000),
            ),
        ]);
        
        $http = $this->fakeHttp();
        $http->request(method: 'GET', uri: 'register');
        $response = $http->response();
        $http->request(method: 'POST', uri: 'register', body: [
            'custom_mtp' => $response->crawl()->filter('input[name="custom_mtp"]')->attr('value'),
            'custom_hp' => '',
        ]);
        
        $this->getApp()->on(ClockInterface::class, function($clock) {
            return new FrozenClock($clock->now()->modify('+2001 milliseconds'));
        });
        
        $app = $this->bootingApp();
        $app->get(RouterInterface::class)->post('register', function (ResponserInterface $responser) {
            return $responser->write(data: '', code: 200);
        })->middleware([
              ProtectAgainstSpam::class,
              'detector' => 'register',
          ]);
        
        $http->response()->assertStatus(200);
    }
    
    public function testMiddlewareWithDetectorFactoryPasses()
    {
        $config = $this->fakeConfig();
        $config->with('spam.detectors', [
            'register' => new Factory\Composite(
                new Factory\Honeypot(inputName: 'custom_hp'),
                new Factory\MinTimePassed(inputName: 'custom_mtp', milliseconds: 2000),
            ),
        ]);
        
        $http = $this->fakeHttp();
        $http->request(method: 'GET', uri: 'register');
        $response = $http->response();
        $http->request(method: 'POST', uri: 'register', body: [
            'custom_mtp' => $response->crawl()->filter('input[name="custom_mtp"]')->attr('value'),
            'custom_hp' => '',
        ]);
        
        $this->getApp()->on(ClockInterface::class, function($clock) {
            return new FrozenClock($clock->now()->modify('+2001 milliseconds'));
        });
        
        $app = $this->bootingApp();
        $app->get(RouterInterface::class)->post('register', function (ResponserInterface $responser) {
            return $responser->write(data: '', code: 200);
        })->middleware([
              ProtectAgainstSpam::class,
              'detector' => new Factory\Composite(
                  new Factory\Named('register'),
                  new Factory\WithoutUrl(inputNames: ['message']),
              ),
          ]);
        
        $http->response()->assertStatus(200);
    }
    
    public function testMiddlewareWithDetectorFactoryFails()
    {
        $config = $this->fakeConfig();
        $config->with('spam.detectors', [
            'register' => new Factory\Composite(
                new Factory\Honeypot(inputName: 'custom_hp'),
                new Factory\MinTimePassed(inputName: 'custom_mtp', milliseconds: 2000),
            ),
        ]);
        
        $http = $this->fakeHttp();
        $http->request(method: 'GET', uri: 'register');
        $response = $http->response();
        $http->request(method: 'POST', uri: 'register', body: [
            'custom_mtp' => $response->crawl()->filter('input[name="custom_mtp"]')->attr('value'),
            'custom_hp' => '',
            'message' => 'https:',
        ]);
        
        $this->getApp()->on(ClockInterface::class, function($clock) {
            return new FrozenClock($clock->now()->modify('+2001 milliseconds'));
        });
        
        $app = $this->bootingApp();
        $app->get(RouterInterface::class)->post('register', function (ResponserInterface $responser) {
            return $responser->write(data: '', code: 200);
        })->middleware([
              ProtectAgainstSpam::class,
              'detector' => new Factory\Composite(
                  new Factory\Named('register'),
                  new Factory\WithoutUrl(inputNames: ['message']),
              ),
          ]);
        
        $http->response()->assertStatus(422);
    }
}