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

namespace App\DataFixtures;

use App\Entity\Bundesliga\BundesligaLineup;
use App\Entity\Bundesliga\BundesligaPlayer;
use App\Entity\Bundesliga\BundesligaSeason;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @copyright   Copyright (C) - 2019 Dr. Holger Maerz
 * @author Dr. H.Maerz <holger@nakade.de>
 */
class BundesligaLineupFixtures extends BaseFixture implements DependentFixtureInterface
{
    protected function loadData(ObjectManager $manager)
    {
        $allSeasons = $this->getReferencesKeysByGroup(BundesligaSeason::class, 'bl_season');
        $count = 0;

        /** @var BundesligaSeason $season */
        foreach ($allSeasons as $seasonKey) {
            $lineup = new BundesligaLineup();
            $season = $this->getReference($seasonKey);
            $lineup->setSeason($season);
            $this->setPlayersLineUp($lineup);

            $manager->persist($lineup);
            // store for usage later as groupName_#COUNT#
            $this->addReference(sprintf('bl_lineup_%d', $count), $lineup);
            ++$count;
        }

        $manager->flush();
    }

    private function setPlayersLineUp(BundesligaLineup $lineup)
    {
        $refName = $this->getPlayers();

        $pos = 1;
        while (count($refName) > 0) {
            $name = array_shift($refName);
            $player = $this->getReference($name);
            $method = 'setPosition'.$pos;
            if (method_exists($lineup, $method)) {
                $lineup->$method($player);
            }
            ++$pos;
        }
    }

    /**
     * @return BundesligaPlayer[]
     *
     * @throws \Exception
     */
    private function getPlayers(): array
    {
        $allPlayers = $this->getReferencesKeysByGroup(BundesligaPlayer::class, 'bl_player');
        shuffle($allPlayers);

        return array_slice($allPlayers, 0, $this->faker->numberBetween(6, 10));
    }

    public function getDependencies()
    {
        return [
            BundesligaSeasonFixtures::class,
            BundesligaPlayerFixtures::class,
        ];
    }
}
