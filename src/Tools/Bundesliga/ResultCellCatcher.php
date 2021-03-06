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

use App\Services\Model\ResultsModel;
use Psr\Log\LoggerInterface;
use Symfony\Component\DomCrawler\Crawler;

class ResultCellCatcher
{
    const TABLE_CELL = 'td';
    const TABLE_LINK = 'a';

    private $season;
    private $league;
    private $matchDay;
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(string $season, string $league, string $matchDay, LoggerInterface $logger)
    {
        $this->season = $season;
        $this->league = $league;
        $this->matchDay = $matchDay;
        $this->logger = $logger;
    }

    /**
     * Find pairing results of the DGoB site if found AND match is played!
     */
    public function extract(\DOMNodeList $childNodes): ?ResultsModel
    {
        $rowCrawler = new Crawler($childNodes);

        $node = $rowCrawler->filter(self::TABLE_CELL)->getNode(0);
        if (!$node) {
            $this->logger->error('No table cells TD found.');

            return null;
        }
        $model = new ResultsModel($this->season, $this->matchDay);

        $model->setPlayedAt($node->textContent);
        $this->logger->notice(
            'Found data for match played at {date}.',
            ['date' => $node->textContent]
        );

        //homeTeam
        $node = $rowCrawler->filter(self::TABLE_CELL)->getNode(2);
        if (!$node || !$node->hasChildNodes()) {
            $this->logger->error('No node found for home team data.');

            return null;
        }
        $model->homeTeam = $this->findTeamName($node->childNodes);

        //awayTeam
        $node = $rowCrawler->filter(self::TABLE_CELL)->getNode(4);
        if (!$node || !$node->hasChildNodes()) {
            $this->logger->error('No node found for away team data.');

            return null;
        }
        $model->awayTeam = $this->findTeamName($node->childNodes);

        //homePoints
        $node = $rowCrawler->filter(self::TABLE_CELL)->getNode(6);
        $homePoints = $node->textContent;

        if (!is_numeric($homePoints)) {
            $this->logger->notice('No numeric value for home points found {points}.', ['points' => $homePoints]);

            return null;
        }
        $model->homePoints = $homePoints;

        //awayPoints
        $node = $rowCrawler->filter(self::TABLE_CELL)->getNode(8);
        $awayPoints = $node->textContent;
        if (!is_numeric($awayPoints)) {
            $this->logger->notice('No numeric value for away points found {points}.', ['points' => $awayPoints]);

            return null;
        }
        $model->awayPoints = $awayPoints;

        return $model;
    }

    private function findTeamName(\DOMNodeList $childNodes): ?string
    {
        $cellCrawler = new Crawler($childNodes);
        $cellNode = $cellCrawler->filter(self::TABLE_LINK)->getNode(0);
        if (!$cellNode || !$cellNode->hasChildNodes()) {
            $this->logger->error('No team link found.');

            return null;
        }
        $teamName = $cellNode->textContent;

        return $this->cleanTeamName($teamName);
    }

    private function cleanTeamName(string $name)
    {
        $name = str_replace('%0A', '', $name);

        return trim($name);
    }
}
