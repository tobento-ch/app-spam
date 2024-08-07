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

namespace Tobento\App\Spam\Test\Exception;

use PHPUnit\Framework\TestCase;
use Tobento\App\Spam\Exception\SpamException;
use RuntimeException;

class SpamExceptionTest extends TestCase
{
    public function testException()
    {
        $this->assertInstanceof(RuntimeException::class, new SpamException());
    }
}