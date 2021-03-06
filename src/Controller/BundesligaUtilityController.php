<?php
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

namespace App\Controller;

use App\Entity\Bundesliga\BundesligaSeason;
use App\Services\BundesligaTableService;
use App\Services\Model\TableModel;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @copyright   Copyright (C) - 2020 Dr. Holger Maerz
 * @author Dr. H.Maerz <holger@nakade.de>
 *
 * Controller for AJAX actions
 */
class BundesligaUtilityController extends AbstractController
{
    /**
     * @Route("/bundesliga/season-select", name="bundesliga_season_select")
     */
    public function getSeasonSelect(BundesligaTableService $tableService, Request $request)
    {
        $seasonId = $request->query->get('seasonId');
        $matchDay = $request->query->get('matchDay');

        /** @var TableModel $model */
        $model = $tableService->retrieveTable($seasonId, $matchDay);
        // no field? Return an empty response
        if (!$model) {
            return new Response(null, 204);
        }

        $allSeasons = $this->getDoctrine()->getRepository(BundesligaSeason::class)->findAll();
        $matches = $model->getResult()->getMatches();

        return $this->render('bundesliga/_archive.season.table.html.twig', [
                'allSeasons' => $allSeasons,
                'matches' => $matches,
                'model' => $model,
        ]);
    }

    /**
     * @Route("/bundesliga/matchDay-select", name="bundesliga_matchDay_select")
     */
    public function getMatchDaySelect(BundesligaTableService $tableService, Request $request)
    {
        $seasonId = $request->query->get('seasonId');
        $matchDay = $request->query->get('matchDay');

        /** @var TableModel $model */
        $model = $tableService->retrieveTable($seasonId, $matchDay);
        // no field? Return an empty response
        if (!$model) {
            return new Response(null, 204);
        }

        return $this->render('bundesliga/_actualTable.html.twig', [
                'model' => $model,
                'url'   => 'bundesliga_matchDay_select',
        ]);
    }
}
