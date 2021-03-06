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

namespace App\Form\DataTransformer;

use App\Entity\Bundesliga\BundesligaTeam;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\DataTransformerInterface;

class TeamTransformer implements DataTransformerInterface
{
    private $finderCallback;
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager, callable $finderCallback)
    {
        $this->finderCallback = $finderCallback;
        $this->entityManager = $entityManager;
    }

    public function transform($value)
    {
        if (null === $value) {
            return '';
        }

        if (!$value instanceof BundesligaTeam) {
            throw new \LogicException('The BundesligaTeamSelectType can only be used with BundesligaTeam objects');
        }

        return $value->getName();
    }

    public function reverseTransform($value)
    {
        $repository = $this->entityManager->getRepository(BundesligaTeam::class);
        $callback = $this->finderCallback;
        $team = $callback($repository, $value);
        if (!$team) {
            $team = new BundesligaTeam();
            $team->setName($value);
            $this->entityManager->persist($team);
        }
        return $team;
    }
}
