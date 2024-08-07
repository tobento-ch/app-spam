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

namespace Tobento\App\Spam\Validation;

use Tobento\App\Spam\DetectorFactoryInterface;
use Tobento\App\Spam\DetectorsInterface;
use Tobento\App\Spam\Exception\SpamDetectedException;
use Tobento\App\Spam\Factory;
use Tobento\Service\Autowire\Autowire;
use Tobento\Service\Validation\Rule\AutowireAware;
use Tobento\Service\Validation\Rule\HasAutowire;
use Tobento\Service\Validation\Rule\Passes;
use Tobento\Service\Validation\Rule\Rule;

/**
 * SpamRule
 */
class SpamRule extends Rule implements AutowireAware
{
    use HasAutowire;
    
    /**
     * @var Passes
     */
    protected Passes $passes;
    
    /**
     * The error messages.
     */
    public const MESSAGES = [
        'passes' => 'The :attribute is detected as spam.',
    ];
    
    /**
     * Create a new SpamRule.
     *
     * @param null|string|DetectorFactoryInterface $detector
     * @param null|bool|callable $skipValidation
     * @param null|string $errorMessage
     */
    final public function __construct(
        null|string|DetectorFactoryInterface $detector = null,
        protected $skipValidation = null,
        protected null|string $errorMessage = null,
    ) {
        $this->passes = new Passes(
            passes: static function (mixed $value, array $parameters, DetectorsInterface $detectors) use ($detector): bool {
                if (is_string($detector)) {
                    $detector = $detectors->get($detector);
                } elseif ($detector instanceof DetectorFactoryInterface) {
                    $name = 'rule';
                    $detector = $detectors->add(name: $name, detector: $detector)->get($name);
                } else {
                    $name = 'rule';
                    $named = [];
                    
                    foreach ($parameters as $detector) {
                        $named[] = new Factory\Named($detector);
                    }
                    
                    $detector = $detectors->add(
                        name: $name,
                        detector: new Factory\Composite(...$named)
                    )->get($name);
                }
                
                try {
                    $detector->detectFromValue(value: $value);
                    return true;
                } catch (SpamDetectedException $e) {
                    return false;
                }
            },
            skipValidation: $skipValidation,
            errorMessage: $errorMessage ?: static::MESSAGES['passes'],
        );
    }
    
    /**
     * Create a new instance.
     *
     * @param null|string|DetectorFactoryInterface $detector
     * @param null|bool|callable $skipValidation
     * @param null|string $errorMessage
     * @return static
     */
    public static function new(
        null|string|DetectorFactoryInterface $detector = null,
        $skipValidation = null,
        null|string $errorMessage = null,
    ): static {
        return new static($detector, $skipValidation, $errorMessage);
    }
    
    /**
     * Skips validation depending on value and rule method.
     * 
     * @param mixed $value The value to validate.
     * @param string $method
     * @return bool Returns true if skip validation, otherwise false.
     */
    public function skipValidation(mixed $value, string $method = 'passes'): bool
    {
        return $this->passes->skipValidation($value, $method);
    }
    
    /**
     * Determine if the validation rule passes.
     * 
     * @param mixed $value The value to validate.
     * @param array $parameters Any parameters used for the validation.
     * @return bool
     */
    public function passes(mixed $value, array $parameters = []): bool
    {
        return $this->passes->passes($value, $parameters);
    }
    
    /**
     * Returns the validation error messages.
     * 
     * @return array
     */
    public function messages(): array
    {
        return $this->passes->messages();
    }

    /**
     * Sets the autowire.
     * 
     * @param Autowire $autowire
     * @return static $this
     */
    public function setAutowire(Autowire $autowire): static
    {
        $this->passes->setAutowire($autowire);
        return $this;
    }
}