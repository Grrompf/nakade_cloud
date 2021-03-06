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

use App\Entity\Bundesliga\BundesligaPlayer;
use App\Entity\Bundesliga\BundesligaSeason;
use App\Entity\Bundesliga\BundesligaTable;
use App\Entity\Bundesliga\LineupMail;
use App\Entity\Bundesliga\ResultMail;
use App\Form\CaptainResultInputType;
use App\Form\CaptainTeamResultsType;
use App\Form\Model\ResultModel;
use App\Form\Model\TeamResultsModel;
use App\Services\ActualTableService;
use App\Services\BundesligaTableCreator;
use App\Services\BundesligaTableService;
use App\Services\BundesligaTableUpdater;
use App\Services\Model\TableModel;
use App\Services\ResultModelCreator;
use App\Services\TeamResultsModelCreator;
use App\Services\TeamStatsService;
use App\Tools\PlayerStats;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BundesligaController extends AbstractController
{
    /**
     * Team lineup and results.
     *
     * @Route("/bundesliga/actualMatchDay", name="bundesliga_actual_matchDay")
     *
     * @IsGranted("ROLE_NAKADE_TEAM")
     */
    public function actualMatchDay(Request $request, ResultModelCreator $modelCreator)
    {
        $model = $modelCreator->create();
        if (!$model) {
            return $this->render('default/about.html.twig');
        }

        $form = $this->createForm(CaptainResultInputType::class, $model);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $model = $form->getData();
            if (!assert($model instanceof ResultModel)) {
                throw new UnexpectedTypeException($model, ResultModel::class);
            }

            foreach ($model->getAllMatches() as $match) {
                $this->getDoctrine()->getManager()->persist($match);
            }

            //create mail if not existing
            if (!$model->getResults()->getResultMail()) {
                $mail = new ResultMail($model->getResults());
                $this->getDoctrine()->getManager()->persist($mail);
            }
            //create mail if not existing
            if (!$model->getResults()->getLineupMail()) {
                $lineupMail = new LineupMail($model->getResults());
                $this->getDoctrine()->getManager()->persist($lineupMail);
            }

            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('success', 'bundesliga.actual.matchDay.update.success');

            return $this->redirect($request->getUri());
        }

        return $this->render(
            'bundesliga/form.matchday.html.twig',
            [
                'form' => $form->createView(),
                'model' => $model,
            ]
        );
    }

    /**
     * @Route("/bundesliga/actualResults", name="bundesliga_actual_results")
     *
     * @IsGranted("ROLE_NAKADE_TEAM")
     */
    public function actualResults(Request $request, TeamResultsModelCreator $modelCreator, BundesligaTableCreator $tableCreator, BundesligaTableUpdater $tableUpdater)
    {
        $model = $modelCreator->create();

        $form = $this->createForm(CaptainTeamResultsType::class, $model);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $model = $form->getData();
            if (!assert($model instanceof TeamResultsModel)) {
                throw new UnexpectedTypeException($model, TeamResultsModel::class);
            }

            foreach ($model->getResults() as $result) {
                $this->getDoctrine()->getManager()->persist($result);
            }

            $tables = $tableCreator->create($model->getResults());
            $tables = $tableUpdater->update($tables);
            foreach ($tables as $table) {
                $this->getDoctrine()->getManager()->persist($table);
            }

            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('success', 'bundesliga.actual.matchDay.update.success');

            return $this->redirect($request->getUri());
        }

        return $this->render(
                    'bundesliga/form.matchday.results.html.twig',
                    [
                            'form' => $form->createView(),
                            'model' => $model,
                    ]
            );
    }

    /**
     * @Route("/bundesliga/actualTable", name="bundesliga_actual_table")
     *
     * @IsGranted("ROLE_NAKADE_TEAM")
     */
    public function actualTable(ActualTableService $tableService): Response
    {
        $model = $tableService->retrieveTable();

        return $this->render('bundesliga/form.matchday.table.html.twig', ['model' => $model]);
    }

    /**
     * @Route("/bundesliga/table/update/{id}/{action}/{type}", name="bundesliga_update_table", requirements={"id"="\d+", "action"="add|remove", "type"="firstBoardPoints|penalty"})
     *
     * @IsGranted("ROLE_USER")
     */
    public function updateTable(int $id, string $action, string $type, BundesligaTableUpdater $updater): RedirectResponse
    {
        $repo = $this->getDoctrine()->getRepository(BundesligaTable::class);
        $table = $repo->find($id);

        if ('add' === $action) {
            if ('firstBoardPoints' === $type) {
                $table->addFirstBoardPoints();
            }
            if ('penalty' === $type) {
                $table->addPenalty();
            }
        }
        if ('remove' === $action) {
            if ('firstBoardPoints' === $type) {
                $table->removeFirstBoardPoints();
            }
            if ('penalty' === $type) {
                $table->removePenalty();
            }
        }
        $this->getDoctrine()->getManager()->flush();
        $tables = $repo->findTablesBySeasonAndMatchDay($table->getBundesligaSeason(), $table->getMatchDay());

        $updater->update($tables);
        $this->getDoctrine()->getManager()->flush();

        return $this->redirectToRoute('bundesliga_actual_table');
    }

    /**
     * @Route("/bundesliga/season/archive", name="bundesliga_season_archive")
     *
     * @IsGranted("ROLE_USER")
     */
    public function showSeasonArchive(BundesligaTableService $tableService, Request $request)
    {
        $seasonId = $matchDay = null;
        if ($request->query->has('seasonId')) {
            $seasonId = $request->query->get('seasonId');
        }
        if ($request->query->has('matchDay')) {
            $matchDay = $request->query->get('matchDay');
        }

        /** @var TableModel $model */
        $model = $tableService->retrieveTable($seasonId, $matchDay);
        $matches = null;
        $allSeasons = $this->getDoctrine()->getRepository(BundesligaSeason::class)->findAll();
        if ($model) {
            $matches = $model->getResult()->getMatches();
        }

        return $this->render('bundesliga/archive.season.html.twig', [
                'allSeasons' => $allSeasons,
                'matches' => $matches,
                'model' => $model,
        ]);
    }

    /**
     * @Route("/bundesliga/archive/player", name="bundesliga_archive_player")
     *
     * @IsGranted("ROLE_USER")
     */
    public function showArchivePlayer(PlayerStats $service, Request $request)
    {
        $seasonId = $playerId = null;
        if ($request->query->has('seasonId')) {
            $seasonId = $request->query->get('seasonId');
        }
        if ($request->query->has('playerId')) {
            $playerId = $request->query->get('playerId');
        }

        $season = $this->getDoctrine()->getRepository(BundesligaSeason::class)->find($seasonId);
        $player = $this->getDoctrine()->getRepository(BundesligaPlayer::class)->find($playerId);

        $model = $service->getStats($season, $player);

        return $this->render('bundesliga/archive.player.html.twig', [
                'model' => $model,
        ]);
    }

    /**
     * @Route("/bundesliga/season/stats/team", name="bundesliga_season_stats_team")
     *
     * @IsGranted("ROLE_USER")
     */
    public function showTeamStats(TeamStatsService $service, Request $request)
    {
        $seasonId = null;
        if ($request->query->has('seasonId')) {
            $seasonId = $request->query->get('seasonId');
        }

        $model = $service->getStats($seasonId);
        $allSeasons = $this->getDoctrine()->getRepository(BundesligaSeason::class)->findAll();

        return $this->render('bundesliga/season.team_stats.html.twig', [
                'allSeasons' => $allSeasons,
                'data' => $model,
        ]);
    }

    /**
     * @Route("/bundesliga/season/stats/player", name="bundesliga_season_stats_player")
     *
     * @IsGranted("ROLE_USER")
     */
    public function showPlayerStats(PlayerStats $service, Request $request)
    {
        $seasonId = $playerId = null;
        if ($request->query->has('seasonId')) {
            $seasonId = $request->query->get('seasonId');
        }
        if ($request->query->has('playerId')) {
            $playerId = $request->query->get('playerId');
        }

        $season = $this->getDoctrine()->getRepository(BundesligaSeason::class)->find($seasonId);
        $player = $this->getDoctrine()->getRepository(BundesligaPlayer::class)->find($playerId);

        $model = $service->getStats($season, $player);

        return $this->render('bundesliga/season.player_stats.html.twig', [
                'model' => $model,
        ]);
    }
}
