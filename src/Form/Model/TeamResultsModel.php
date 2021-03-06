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

namespace App\Form\Model;

use App\Entity\Bundesliga\BundesligaResults;
use App\Entity\Bundesliga\BundesligaSeason;
use App\Validator\TeamResult;

/**
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @copyright   Copyright (C) - 2019 Dr. Holger Maerz
 * @author Dr. H.Maerz <holger@nakade.de>
 *
 * @TeamResult()
 */
class TeamResultsModel
{
    /** @var array|BundesligaResults[] */
    private $results;
    private $season;
    private $matchDay;

    public function __construct(array $matchDayResults)
    {
        /* @var BundesligaResults[] $matchDayResults */
        $this->season = $matchDayResults[0]->getSeason();
        $this->matchDay = $matchDayResults[0]->getMatchDay();
        $this->results = $matchDayResults;
    }

    public function getResults(): array
    {
        return $this->results;
    }

    public function getNoResults(): int
    {
        if (!$this->results) {
            return 0;
        }

        return count($this->results);
    }

    public function getSeason(): ?BundesligaSeason
    {
        return $this->season;
    }

    public function getMatchDay(): ?int
    {
        return $this->matchDay;
    }

    public function getMatch1(): ?BundesligaResults
    {
        return $this->getResults()[0];
    }

    public function setMatch1(BundesligaResults $result): self
    {
        $this->getResults()[1] = $result;

        return $this;
    }

    public function getMatch2(): ?BundesligaResults
    {
        return $this->getResults()[1];
    }

    public function setMatch2(BundesligaResults $result): self
    {
        $this->getResults()[2] = $result;

        return $this;
    }

    public function getMatch3(): ?BundesligaResults
    {
        return $this->getResults()[2];
    }

    public function setMatch3(BundesligaResults $result): self
    {
        $this->getResults()[3] = $result;

        return $this;
    }

    public function getMatch4(): ?BundesligaResults
    {
        return $this->getResults()[3];
    }

    public function setMatch4(BundesligaResults $result): self
    {
        $this->getResults()[4] = $result;

        return $this;
    }

    public function getMatch5(): ?BundesligaResults
    {
        return $this->getResults()[4];
    }

    public function isComplete(): bool
    {
        foreach ($this->results as $result) {
            if (!$result->getBoardPointsHome() || !$result->getBoardPointsAway()) {
                return false;
            }
            if (0 === $result->getBoardPointsHome() && 0 === $result->getBoardPointsAway()) {
                return false;
            }
        }

        return true;
    }
}
