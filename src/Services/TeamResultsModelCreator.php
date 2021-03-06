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

namespace App\Services;

use App\Form\Model\TeamResultsModel;
use App\Repository\Bundesliga\BundesligaResultsRepository;
use App\Repository\Bundesliga\BundesligaSeasonRepository;

/**
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @copyright   Copyright (C) - 2020 Dr. Holger Maerz
 * @author Dr. H.Maerz <holger@nakade.de>
 */
class TeamResultsModelCreator
{
    private $resultsRepository;
    private $seasonRepository;

    public function __construct(BundesligaSeasonRepository $seasonRepository, BundesligaResultsRepository $resultsRepository)
    {
        $this->resultsRepository = $resultsRepository;
        $this->seasonRepository = $seasonRepository;
    }

    public function create(): ?TeamResultsModel
    {
        $actualSeason = $this->seasonRepository->findOneBy(['actualSeason' => true]);
        if (!$actualSeason) {
            return null;
        }

        $matchDay = $this->resultsRepository->findActualMatchDay($actualSeason);
        if (!$matchDay) {
            return null;
        }

        $results = $this->resultsRepository->findResultsByMatchDay($actualSeason, (int) $matchDay);
        if (!$results) {
            return null;
        }

        return new TeamResultsModel($results);
    }
}
