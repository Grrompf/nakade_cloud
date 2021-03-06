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

namespace App\Services;

use App\Entity\Bundesliga\BundesligaResults;
use App\Entity\Bundesliga\BundesligaSeason;
use App\Services\Model\TableModel;

/**
 * Find the actual table by internal database search
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @copyright   Copyright (C) - 2019 Dr. Holger Maerz
 * @author Dr. H.Maerz <holger@nakade.de>
 */
class ActualTableService extends AbstractTableService
{
    public function retrieveTable(int $matchDay = null): ?TableModel
    {
        $actualSeason = $this->findActualSeason();

        if (!$actualSeason) {
            return null;
        }

        $lastMatchDay = $this->findLastMatchDay($actualSeason);
        if (!$lastMatchDay) {
            return null;
        }

        if (!$matchDay || (int) $matchDay > (int) $lastMatchDay) {
            $matchDay = $lastMatchDay;
        }

        $model = new TableModel($actualSeason, (int) $matchDay, $lastMatchDay);

        $matchDayRange = $this->calcMatchDayRange($actualSeason);
        $model->setMatchDayRange($matchDayRange);

        $table = $this->findActualTable($actualSeason, $matchDay);
        if (!$table) {
            return null;
        }

        $result = $this->manager->getRepository(BundesligaResults::class)->findNakadeResult($actualSeason->getId(), (int) $matchDay);
        if ($result) {
            $model->setResult($result);
        }
        $nextResult = $this->manager->getRepository(BundesligaResults::class)->findNakadeResult($actualSeason->getId(), (int) $matchDay + 1);
        if ($nextResult) {
            $model->setNextResult($nextResult);
        }

        return $model->setActualTable($table);
    }

    private function calcMatchDayRange(BundesligaSeason $actualSeason): array
    {
        $maxMatchDays = $actualSeason->getTeams()->count() - 1;

        return range(1, $maxMatchDays);
    }
}
