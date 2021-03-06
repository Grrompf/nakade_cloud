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

use App\Validator\SeasonTitle;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Bundesliga\BundesligaSeasonRepository")
 * @ORM\EntityListeners({"App\Entity\Listener\BundesligaSeasonListener"})
 *
 * @UniqueEntity(
 *     fields={"title"},
 *     message="season.unique"
 * )
 */
class BundesligaSeason
{
    use TimestampableEntity;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @SeasonTitle()
     *
     * @ORM\Column(type="string", length=255, unique=true)
     */
    private $title;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $startAt;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $endAt;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Bundesliga\BundesligaMatch", mappedBy="season", fetch="EXTRA_LAZY")
     */
    private $matches;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Bundesliga\BundesligaTeam", inversedBy="seasons", fetch="EXTRA_LAZY")
     */
    private $teams;

    /**
     * @Assert\NotBlank
     *
     * @ORM\Column(type="string", length=255)
     */
    private $league;

    /**
     * Ligaleiter.
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Bundesliga\BundesligaExecutive")
     */
    private $executive;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Bundesliga\BundesligaLineup", mappedBy="season", fetch="EXTRA_LAZY")
     */
    private $lineup;

    /**
     * @ORM\Column(type="boolean", options={"default": 0})
     */
    private $actualSeason = false;

    public function __construct()
    {
        $this->matches = new ArrayCollection();
        $this->teams = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getStartAt(): ?\DateTimeInterface
    {
        return $this->startAt;
    }

    public function setStartAt(?\DateTimeInterface $startAt): self
    {
        $this->startAt = $startAt;

        return $this;
    }

    public function getEndAt(): ?\DateTimeInterface
    {
        return $this->endAt;
    }

    public function setEndAt(?\DateTimeInterface $endAt): self
    {
        $this->endAt = $endAt;

        return $this;
    }

    /**
     * @return Collection|BundesligaMatch[]
     */
    public function getMatches(): Collection
    {
        return $this->matches;
    }

    public function addMatch(BundesligaMatch $match): self
    {
        if (!$this->matches->contains($match)) {
            $this->matches[] = $match;
            $match->setSeason($this);
        }

        return $this;
    }

    public function removeMatch(BundesligaMatch $match): self
    {
        if ($this->matches->contains($match)) {
            $this->matches->removeElement($match);
            // set the owning side to null (unless already changed)
            if ($match->getSeason() === $this) {
                $match->setSeason(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|BundesligaTeam[]
     */
    public function getTeams(): Collection
    {
        return $this->teams;
    }

    public function addTeam(BundesligaTeam $team): self
    {
        if (!$this->teams->contains($team)) {
            $this->teams[] = $team;
        }

        return $this;
    }

    public function removeTeam(BundesligaTeam $team): self
    {
        if ($this->teams->contains($team)) {
            $this->teams->removeElement($team);
        }

        return $this;
    }

    public function getLeague(): ?string
    {
        return $this->league;
    }

    public function setLeague(string $league): self
    {
        $this->league = $league;

        return $this;
    }

    public function getExecutive(): ?BundesligaExecutive
    {
        return $this->executive;
    }

    public function setExecutive(?BundesligaExecutive $executive): self
    {
        $this->executive = $executive;

        return $this;
    }

    public function getLineup(): ?BundesligaLineup
    {
        return $this->lineup;
    }

    public function setLineup(?BundesligaLineup $lineup): self
    {
        $this->lineup = $lineup;

        return $this;
    }

    public function isActualSeason(): bool
    {
        return $this->actualSeason;
    }

    public function setActualSeason(bool $actualSeason): self
    {
        $this->actualSeason = $actualSeason;

        return $this;
    }

    public function getDGoBIndex()
    {
        $pattern = '#.*(\d{4})\/(\d{2})#';
        preg_match($pattern, $this->getTitle(), $matches);

        return sprintf("%s_20%s", $matches[1], $matches[2]);
    }

    public function getSgfDir()
    {
        $pattern = '#.*(\d{4})\/(\d{2})#';
        preg_match($pattern, $this->getTitle(), $matches);

        return sprintf("%s_%s", $matches[1], $matches[2]);
    }

    public function __toString()
    {
        return $this->title;
    }
}
