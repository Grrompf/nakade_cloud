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

namespace App\MessageHandler;

use App\Message\ConfirmContact;
use App\Message\ReplyContact;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Swift_Mailer;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Twig\Environment;

/**
 * Class ReplyContactHandler!
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @copyright   Copyright (C) - 2019 Dr. Holger Maerz
 * @author Dr. H.Maerz <holger@nakade.de>
 */
class ReplyContactHandler implements MessageHandlerInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    private $mailer;
    private $twig;
    private $emailService;

    public function __construct(Swift_Mailer $mailer, Environment $twig, string $emailService)
    {
        $this->mailer = $mailer;
        $this->twig = $twig;
        $this->emailService = $emailService;
    }

    public function __invoke(ReplyContact $replyContact)
    {
        $contactReply = $replyContact->getContactReply();
        $email = $contactReply->getRecipient()->getEmail();

        $message = (new \Swift_Message('Antwort auf Ihre Kontaktanfrage bei [nakade.de]'))
                    ->setFrom($this->emailService)
                    ->setTo($email)
                    ->setBody(
                        $this->twig->render(
                            // templates/emails/registration.html.twig
                                    'emails/contactReply.html.twig',
                            ['email' => $email, 'reply' => $contactReply]
                        ),
                        'text/html'
                    )

                    // you can remove the following code if you don't define a text version for your emails
                    ->addPart(
                        $this->twig->render(
                            // templates/emails/registration.txt.twig
                                    'emails/contactReply.txt.twig',
                            ['email' => $email, 'reply' => $contactReply]
                        ),
                        'text/plain'
                    );

        $sent = $this->mailer->send($message);

        if (0 === $sent) {
            if ($this->logger) {
                $this->logger->alert(sprintf('Could not sent confirmation mail id:%d', $contactMail->getId()));
            }
        }
    }
}
