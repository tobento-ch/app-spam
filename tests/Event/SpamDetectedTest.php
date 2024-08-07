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

namespace Tobento\App\Spam\Test\Event;

use PHPUnit\Framework\TestCase;
use Nyholm\Psr7\Factory\Psr17Factory;
use Tobento\App\Spam\Detector;
use Tobento\App\Spam\Exception\SpamDetectedException;
use Tobento\App\Spam\Event\SpamDetected;

class SpamDetectedTest extends TestCase
{
    public function testEvent()
    {
        $exception = new SpamDetectedException(
            detector: new Detector\NullDetector(name: 'null')
        );
        
        $request = (new Psr17Factory())->createServerRequest('GET', 'uri', []);
        
        $event = new SpamDetected(
            exception: $exception,
            request: $request,
        );
        
        $this->assertTrue($exception === $event->exception());
        $this->assertTrue($request === $event->request());
    }
}