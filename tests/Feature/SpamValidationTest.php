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
use Tobento\App\AppInterface;
use Tobento\App\Spam\Detector;
use Tobento\App\Spam\DetectorInterface;
use Tobento\App\Spam\Factory;
use Tobento\App\Validation\Http\ValidationRequest;
use Tobento\Service\Responser\ResponserInterface;
use Tobento\Service\Routing\RouterInterface;

class SpamValidationTest extends \Tobento\App\Testing\TestCase
{
    public function createApp(): AppInterface
    {
        $app = $this->createTmpApp(rootDir: __DIR__.'/../..');
        $app->boot(\Tobento\App\Http\Boot\RequesterResponser::class);
        $app->boot(\Tobento\App\Http\Boot\Routing::class);
        $app->boot(\Tobento\App\Validation\Boot\HttpValidationErrorHandler::class);
        $app->boot(\Tobento\App\Spam\Boot\ValidationSpamRule::class);
        $app->boot(\Tobento\App\Spam\Boot\Spam::class);
        
        return $app;
    }

    public function testWithStringRulePasses()
    {
        $http = $this->fakeHttp();
        $http->request(method: 'POST', uri: 'register', body: [
            'email' => 'foo@example.com',
        ]);
        
        $app = $this->bootingApp();
        $app->get(RouterInterface::class)->post('register', function (ValidationRequest $request, ResponserInterface $responser) {
            $validation = $request->validate(
                rules: [
                    'email' => 'required|spam:emailDomain',
                ],
                throwExceptionOnFailure: false,
            );
            
            if ($validation->isValid()) {
                return $responser->write(data: '', code: 200);
            }
            
            $error = $validation->errors()->key('email')->first();
            return $responser->write(data: $error->message(), code: 403);
        });
        
        $http->response()->assertStatus(200);
    }
    
    public function testWithStringRuleFails()
    {
        $config = $this->fakeConfig();
        $config->with('spam.detectors', [
            'emailDomain' => static function (string $name): DetectorInterface {
                return new Detector\EmailDomain(
                    name: $name,
                    inputName: '',
                    blacklist: ['example.com'],
                    whitelist: [],
                );
            },
        ]);
        
        $http = $this->fakeHttp();
        $http->request(method: 'POST', uri: 'register', body: [
            'email' => 'foo@example.com',
        ]);
        
        $app = $this->bootingApp();
        $app->get(RouterInterface::class)->post('register', function (ValidationRequest $request, ResponserInterface $responser) {
            $validation = $request->validate(
                rules: [
                    'email' => 'required|spam:emailDomain',
                ],
                throwExceptionOnFailure: false,
            );
            
            if ($validation->isValid()) {
                return $responser->write(data: '', code: 200);
            }
            
            $error = $validation->errors()->key('email')->first();
            return $responser->write(data: $error->message(), code: 403);
        });
        
        $http->response()->assertStatus(403)->assertBodySame('The email is detected as spam.');
    }
}