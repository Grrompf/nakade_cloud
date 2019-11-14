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

namespace App\Tools\Bundesliga;

use App\Services\Snoopy;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DomCrawler\Crawler;

class TableCatcher
{
    const DGOB_URI = 'http://www.dgob.de/lmo/lmo.php';
    const SEASON_PATTERN = '#^20(\d{2})_20(\d{2})#';
    const DEFAULT_PARAM = '?action=results&tabtype=0';
    const CSS_SELECTOR = 'table.lmoInner';

    private $snoopy;
    /**
     * @var EntityManagerInterface
     */
    private $manager;

    public function __construct(EntityManagerInterface $manager)
    {
        $this->snoopy = new Snoopy();
        $this->manager = $manager;
    }

    public function extract(string $season, string $league, string $matchDay, $actualSeason = true): array
    {
        //http://www.dgob.de/lmo/lmo.php?action=results&tabtype=0&file=Saison_2013_2014/1314_bl2.l98&st=3
        $linkParams = $this->createLinkParams($season, $league, $matchDay, $actualSeason);
        $this->snoopy->fetch(self::DGOB_URI.$linkParams);
        $html = $this->snoopy->results;

        $crawler = new Crawler($html);
        $domNode = $crawler->filter(self::CSS_SELECTOR)->getNode(1);

        $cellCatcher = new CellCatcher($season, $league, $matchDay);

        $data = [];
        $trCrawler = new Crawler($domNode->childNodes);
        /** @var DOMNode $rowNode */
        $iterator = $trCrawler->getIterator();
        foreach ($iterator as $key => $rowNode) {
            if ($key < 3) {
                continue;
            }

            $model = $cellCatcher->extract($rowNode->childNodes);

            if ($model) {
                $this->manager->persist($model);
                $data[] = $model;
            }
        }
        $this->manager->flush();

        return $data;
    }

    private function createLinkParams(string $season, string $league, string $matchDay, bool $actualSeason)
    {
        if (false === preg_match(self::SEASON_PATTERN, $season, $matches)) {
            throw new \LogicException('Unexpected season format "%s"!', $season);
        }

        $seasonParam = $matches[1].$matches[2];

        $leagueParam = sprintf('%s_bl%s', $seasonParam, $league);
        $fileParam = sprintf('Saison_%s', $season);

        $linkParam = sprintf('&file=%s/%s.l98&st=%s', $fileParam, $leagueParam, $matchDay);
        if ($actualSeason) {
            $linkParam = sprintf('&file=%s.l98&st=%s', $leagueParam, $matchDay);
        }

        return self::DEFAULT_PARAM.$linkParam;
    }
}
