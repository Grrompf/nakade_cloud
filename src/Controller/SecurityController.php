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

use App\Entity\NewsReader;
use App\Entity\User;
use App\Form\Model\UserRegistrationFormModel;
use App\Form\RegisterType;
use App\Message\ConfirmRegistration;
use App\Security\LoginFormAuthenticator;
use App\Security\LoginUtils;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;

/**
 * Class SecurityController!
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @copyright   Copyright (C) - 2019 Dr. Holger Maerz
 * @author Dr. H.Maerz <holger@nakade.de>
 */
class SecurityController extends AbstractController
{
    /**
     * @Route("/login", name="app_login")
     */
    public function login(LoginUtils $authenticationUtils, string $siteKey): Response
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();
        //show reCaptcha
        $isCaptcha = $authenticationUtils->isReCaptcha();

        return $this->render('security/login.html.twig', [
                'last_username' => $lastUsername,
                'isCaptcha' => $isCaptcha,
                'error' => $error,
                'site_key' => $siteKey,
        ]);
    }

    /**
     * @Route("/logout", name="app_logout")
     */
    public function logout()
    {
        //DO NOT DELETE
        //used by authentication service for logout
    }

    /**
     * @Route("/register", name="app_register")
     */
    public function register(Request $request, UserPasswordEncoderInterface $passwordEncoder, GuardAuthenticatorHandler $guardHandler, LoginFormAuthenticator $formAuthenticator, MessageBusInterface $messageBus): Response
    {
        $form = $this->createForm(RegisterType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UserRegistrationFormModel $userModel */
            $userModel = $form->getData();

            $user = new User();
            $user->setEmail($userModel->email)
                    ->setFirstName($userModel->firstName)
                    ->setLastName($userModel->lastName)
                    ->setNewsletter($userModel->newsletter)
                    ->setPassword($passwordEncoder->encodePassword(
                        $user,
                        $userModel->plainPassword
                    ));

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);

            //check if user already subscribed news
            $subscriber = $entityManager->getRepository(NewsReader::class)->findOneBy(['email' => $userModel->email]);
            if ($subscriber) {
                $user->setNewsletter(true);
            } elseif (true === $userModel->newsletter) {
                $reader = new NewsReader();

                $reader->setEmail($userModel->email)
                        ->setFirstName($userModel->firstName)
                        ->setLastName($userModel->lastName)
                ;
                $entityManager->persist($reader);
            }
            $entityManager->flush();

            //mail handling
            $message = new ConfirmRegistration($user);
            $messageBus->dispatch($message);

            $this->addFlash('success', 'Du bist registriert!');

            return $guardHandler->authenticateUserAndHandleSuccess(
                $user,
                $request,
                $formAuthenticator,
                'main'
            );
        }

        return $this->render('security/register.html.twig', [
                'registerForm' => $form->createView(),
        ]);
    }

    /**
     * Confirm the registered email!
     *
     * @Route("/confirm/{token}", name="app_confirm")
     */
    public function confirm(string $token): Response
    {
        $user = $this->getDoctrine()->getRepository(User::class)->findOneBy(['confirmToken' => $token]);
        if (!$user) {
            throw new NotFoundHttpException('Data not found!');
        }

        if (false === $user->isConfirmed()) {
            $user->setConfirmed(true);
        }

        //if user is subscribed he is a confirmed reader, too
        $reader = $this->getDoctrine()->getRepository(NewsReader::class)->findOneBy(['email' => $user->getEmail()]);
        if ($reader) {
            $reader->setConfirmed(true);
        }

        $this->getDoctrine()->getManager()->flush();
        $this->addFlash('success', 'Deine email wurde bestätigt!');

        return $this->render('security/confirm.html.twig', ['email' => $user->getEmail()]);
    }
}
