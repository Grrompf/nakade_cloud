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

namespace App\Form;

use App\Entity\Bundesliga\BundesligaMatch;
use App\Entity\Bundesliga\BundesligaPlayer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @copyright   Copyright (C) - 2019 Dr. Holger Maerz
 * @author Dr. H.Maerz <holger@nakade.de>
 */
class CaptainMatchInputType extends AbstractType
{
    private $entityManager;
    private $authorizationChecker;

    public function __construct(EntityManagerInterface $entityManager, AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->entityManager = $entityManager;
        $this->authorizationChecker = $authorizationChecker;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('player', EntityType::class, [
            'class' => BundesligaPlayer::class,
            'placeholder' => 'bundesliga.nakade.player.choose',
            'disabled' => $this->getDisabled(),
        ])
                ->add('opponent', BundesligaOpponentSelectType::class, ['required' => false, 'disabled' => $this->getDisabled()])
                ->add('result', CaptainSelectResultType::class, ['required' => false, 'disabled' => $this->getDisabled()])
                ->add('winByDefault', CheckboxType::class, ['required' => false, 'disabled' => $this->getDisabled()])
                ->add('targetDate', DateTimeType::class, ['widget' => 'single_text', 'required' => false])
        ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            /** @var BundesligaMatch $match */
            $match = $event->getData();
            $form = $event->getForm();
            if ($match->getSeason()->getLineup()->getPlayers()) {
                $choices = $match->getSeason()->getLineup()->getPlayers();

                $form->remove('player');
                $form->add('player', EntityType::class, [
                        'class' => BundesligaPlayer::class,
                        'placeholder' => 'bundesliga.nakade.player.choose',
                        'disabled' => $this->getDisabled(),
                        'choices' => $choices,
                ]);
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
             'data_class' => BundesligaMatch::class,
        ]);
    }

    private function getDisabled(): string
    {
        return $this->authorizationChecker->isGranted('ROLE_NAKADE_TEAM_MANAGER') ? '' : 'disabled';
    }
}
