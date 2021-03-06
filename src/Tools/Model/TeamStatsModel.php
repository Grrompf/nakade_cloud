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

namespace App\Tools\Model;

use App\Entity\Bundesliga\BundesligaSeason;

class TeamStatsModel
{
    private $season;
    private $lastMatchDay;
    private $players = [];

    public function __construct(BundesligaSeason $season)
    {
        $this->season = $season;
    }

    public function getSeason(): BundesligaSeason
    {
        return $this->season;
    }

    public function setLastMatchDay(string $lastMatchDay): self
    {
        $this->lastMatchDay = $lastMatchDay;

        return $this;
    }

    public function getLastMatchDay(): ?string
    {
        return $this->lastMatchDay;
    }

    public function addPlayer(PlayerStatsModel $model): self
    {
        $this->players[] = $model;

        return $this;
    }

    /**
     * @return PlayerStatsModel[]
     */
    public function getPlayers(): array
    {
        usort($this->players, [$this, 'sortByGames']);

        return $this->players;
    }

    public function sortByGames(PlayerStatsModel $alice, PlayerStatsModel $bob)
    {
        if ($alice->games === $bob->games) {
            return $this->sortByPoints($alice, $bob);
        }

        return $alice->games < $bob->games;
    }

    public function sortByPoints(PlayerStatsModel $alice, PlayerStatsModel $bob)
    {
        if ($alice->points === $bob->points) {
            return false;
        }

        return $alice->points < $bob->points;
    }
}
