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

namespace App\Entity\Bundesliga;

use App\Validator\UniqueMatchBoard;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Bundesliga\BundesligaRelegationMatchRepository")
 *
 *  @UniqueEntity(
 *     fields={"season", "player", "opponent"},
 *     message="match.pairing.unique"
 * )
 *
 * @UniqueMatchBoard()
 */
class BundesligaRelegationMatch extends AbstractMatch
{
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Bundesliga\BundesligaRelegation", inversedBy="matches")
     * @ORM\JoinColumn(nullable=true)
     */
    private $results;

    public function getResults(): ?ResultsInterface
    {
        return $this->results;
    }

    public function setResults(?ResultsInterface $bundesligaResults): self
    {
        $this->results = $bundesligaResults;

        return $this;
    }

    public function isHomeMatch(): bool
    {
        return false !== stripos($this->getResults()->getHome()->getName(), 'Nakade');
    }
}
