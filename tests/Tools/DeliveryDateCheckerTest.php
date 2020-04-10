<?php

declare(strict_types=1);
/**
 * @license MIT License <https://opensource.org/licenses/MIT>
 *
 * Copyright (c) 2020 Dr. Holger Maerz
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated
 * documentation files (the "Software"), to deal in the Software without restriction, including without limitation the
 * rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all copies or substantial portions of the
 * Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE
 * WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR
 * COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR
 * OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

namespace App\Tests\Tools;

use App\Tools\DeliveryDateChecker;
use PHPUnit\Framework\TestCase;

/**
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @copyright   Copyright (C) - 2020 Dr. Holger Maerz
 * @author Dr. H.Maerz <holger@nakade.de>
 */
class DeliveryDateCheckerTest extends TestCase
{
    private $object = null;

    public function setUp(): void
    {
        $this->object = new DeliveryDateChecker();
    }

    /**
     * @dataProvider actualProvider
     */
    public function testIsPassed(\DateTime $sendDate, \DateTime $today, bool $expected)
    {
        $this->assertEquals($expected, $this->object->isDelayed($sendDate, $today));
    }

    public function actualProvider(): array
    {
        return [
            [new \DateTime('2020-04-11'), new \DateTime('2020-04-12'), true],
            [new \DateTime('2020-04-13'), new \DateTime('2020-04-12'), false],
        ];
    }
}
