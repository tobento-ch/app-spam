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

namespace Tobento\App\Spam\Test\Validation;

use PHPUnit\Framework\TestCase;
use Tobento\App\Spam\Detector;
use Tobento\App\Spam\DetectorsInterface;
use Tobento\App\Spam\Detectors;
use Tobento\App\Spam\Factory;
use Tobento\App\Spam\Validation\SpamRule;
use Tobento\Service\Autowire\Autowire;
use Tobento\Service\Container\Container;
use Tobento\Service\Validation\RuleInterface;
use Tobento\Service\Validation\Rule\AutowireAware;

class SpamRuleTest extends TestCase
{
    public function testThatImplementsInterfaces()
    {
        $rule = new SpamRule();
        
        $this->assertInstanceof(RuleInterface::class, $rule);
        $this->assertInstanceOf(AutowireAware::class, $rule);
    }
    
    public function testPassesWithNamedDetector()
    {
        $container = new Container();
        $container->set(DetectorsInterface::class, function () use ($container) {
            return new Detectors($container, [
                'emailDomain' => new Detector\EmailDomain(
                    name: 'emailDomain',
                    inputName: '',
                    blacklist: ['mail.ru'],
                    whitelist: [],
                ),
            ]);
        });
        
        $rule = new SpamRule(detector: 'emailDomain');
        $rule->setAutowire(new Autowire($container));
        
        $this->assertTrue($rule->passes('info@example.com'));
        $this->assertFalse($rule->passes('info@mail.ru'));
    }
    
    public function testPassesWithDetectorFactory()
    {
        $container = new Container();
        $container->set(DetectorsInterface::class, function () use ($container) {
            return new Detectors($container, [
                'emailDomain' => new Detector\EmailDomain(
                    name: 'emailDomain',
                    inputName: '',
                    blacklist: ['mail.ru'],
                    whitelist: [],
                ),
            ]);
        });
        
        $rule = new SpamRule(detector: new Factory\WithoutUrl(inputNames: []));
        $rule->setAutowire(new Autowire($container));
        
        $this->assertTrue($rule->passes(''));
        $this->assertTrue($rule->passes('lorem ipsum'));
        $this->assertFalse($rule->passes('lorem http:'));
        $this->assertFalse($rule->passes('lorem https:'));
    }
    
    public function testPassesWithNullDetectorUsesParameters()
    {
        $container = new Container();
        $container->set(DetectorsInterface::class, function () use ($container) {
            return new Detectors($container, [
                'emailDomain' => new Detector\EmailDomain(
                    name: 'emailDomain',
                    inputName: '',
                    blacklist: ['mail.ru'],
                    whitelist: [],
                ),
            ]);
        });
        
        $rule = new SpamRule(detector: null);
        $rule->setAutowire(new Autowire($container));
        
        $this->assertTrue($rule->passes('info@example.com', ['emailDomain']));
        $this->assertFalse($rule->passes('info@mail.ru', ['emailDomain']));
    }
    
    public function testSkipValidationWithBoolTrueDoesSkip()
    {
        $container = new Container();
        $container->set(DetectorsInterface::class, function () use ($container) {
            return new Detectors($container, [
                'emailDomain' => new Detector\EmailDomain(
                    name: 'emailDomain',
                    inputName: '',
                    blacklist: ['mail.ru'],
                    whitelist: [],
                ),
            ]);
        });
        
        $rule = new SpamRule(detector: 'emailDomain', skipValidation: true);
        $rule->setAutowire(new Autowire($container));
        
        $this->assertTrue($rule->skipValidation('info@mail.ru'));
    }
    
    public function testSkipValidationWithCallable()
    {
        $container = new Container();
        $container->set(DetectorsInterface::class, function () use ($container) {
            return new Detectors($container, [
                'emailDomain' => new Detector\EmailDomain(
                    name: 'emailDomain',
                    inputName: '',
                    blacklist: ['mail.ru'],
                    whitelist: [],
                ),
            ]);
        });
        
        $rule = new SpamRule(detector: 'emailDomain', skipValidation: function(mixed $value): bool {
            return true;
        });
        $rule->setAutowire(new Autowire($container));
        
        $this->assertTrue($rule->skipValidation('info@mail.ru'));
    }

    public function testMessagesMethodWithErrorMessage()
    {
        $rule = new SpamRule(detector: 'emailDomain', errorMessage: 'Message');
        
        $this->assertSame('Message', $rule->messages()['passes'] ?? null);
    }
}