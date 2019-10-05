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

namespace App\Controller;

use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Controller\EasyAdminController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * Class AdminController!
 *
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 *
 * @copyright   Copyright (C) - 2019 Dr. Holger Maerz
 *
 * @author Dr. H.Maerz <holger@nakade.de>
 */
class AdminController extends EasyAdminController
{
    /**
     * @var UserPasswordEncoderInterface
     */
    private $passwordEncoder;

    /**
     * @var \Swift_Mailer
     */
    private $mailer;

    /**
     * AdminController constructor.
     *
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @param \Swift_Mailer                $mailer
     */
    public function __construct(UserPasswordEncoderInterface $passwordEncoder, \Swift_Mailer $mailer)
    {
        $this->passwordEncoder = $passwordEncoder;
        $this->mailer = $mailer;
    }

    /**
     * @param User $user
     */
    protected function persistUserEntity(User $user)
    {
        $encodedPassword = $this->passwordEncoder->encodePassword($user, $user->getPassword());
        $user->setActive(true);
        $user->setPassword($encodedPassword);
        $user->setConfirmToken(uniqid('nakade', true));

        parent::persistEntity($user);
        $this->sendConfirmationMail($user);

        $this->addFlash('success', 'Neuer User angelegt!');
    }

    /**
     * @param User $user
     */
    private function sendConfirmationMail(User $user)
    {
        $message = (new \Swift_Message('Bestätige deine email Adresse'))
                ->setFrom('noreply@nakade.de')
                ->setTo($user->getEmail())
                ->setBody(
                    $this->renderView(
                        // templates/emails/confirmation.html.twig
                                'confirmRegistration.html.twig',
                        ['user' => $user]
                    ),
                    'text/html'
                )

                // you can remove the following code if you don't define a text version for your emails
                ->addPart(
                    $this->renderView(
                        // templates/emails/confirmation.txt.twig
                                'confirmRegistration.txt.twig',
                        ['user' => $user]
                    ),
                    'text/plain'
                );

        $this->mailer->send($message);
    }
}
