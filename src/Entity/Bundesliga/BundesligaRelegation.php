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

namespace App\Entity\Bundesliga;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Bundesliga\BundesligaRelegationRepository")
 */
class BundesligaRelegation extends AbstractResults
{
    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Bundesliga\BundesligaRelegationMatch", mappedBy="results")
     */
    private $matches;

    public function __construct()
    {
        $this->matches = new ArrayCollection();
    }

    /**
     * @return Collection|MatchInterface[]
     */
    public function getMatches(): Collection
    {
        return $this->matches;
    }

    public function addMatch(MatchInterface $match): self
    {
        if (!$this->matches->contains($match)) {
            $this->matches[] = $match;
            $match->setResults($this);
        }

        return $this;
    }

    public function removeMatch(MatchInterface $match): self
    {
        if ($this->matches->contains($match)) {
            $this->matches->removeElement($match);
            // set the owning side to null (unless already changed)
            if ($match->getResults() === $this) {
                $match->setResults(null);
            }
        }

        return $this;
    }
}
