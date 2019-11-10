<?php

declare(strict_types=1);
/**
 * @license MIT License <https://opensource.org/licenses/MIT>
 *
 * Copyright (c) 2019 Dr. Holger Maerz
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

namespace App\Tools\DGoB;

use App\Tools\DGoB\Model\PairingModel;

class PairingsCatcher
{
    public function extract(array $matchData): PairingModel
    {
        $model = new PairingModel();
        $board = 1;
        foreach ($matchData as $data) {
            //whitespaces
            if (empty($data)) {
                continue;
            }

            $kgsId = (new KGSidCatcher())->extract($data);
            if ($kgsId) {
                $model->kgsIdHome = array_shift($kgsId);
                $model->kgsIdAway = array_shift($kgsId);
            }

            //"2:0 (s) Darius Dobranis 2d - Malte Kracht 2d "
            $match = (new MatchCatcher())->extract($data);
            if ($match) {
                $match->board = $board;
                $model->addMatch($match);
                ++$board;
            }
        }

        return $model;
    }
}
