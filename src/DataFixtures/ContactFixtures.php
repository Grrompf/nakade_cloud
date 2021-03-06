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

namespace App\DataFixtures;

use App\Entity\ContactMail;
use App\Entity\User;
use App\Entity\ContactReply;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @copyright   Copyright (C) - 2019 Dr. Holger Maerz
 * @author Dr. H.Maerz <holger@nakade.de>
 */
class ContactFixtures extends BaseFixture implements DependentFixtureInterface
{
    protected function loadData(ObjectManager $manager)
    {
        $this->createMany(20, 'main_contact', function ($i) {
            $contact = new ContactMail();
            $contact->setEmail($this->faker->email);
            $contact->setFirstName($this->faker->firstName);
            $contact->setLastName($this->faker->lastName);
            $contact->setZipCode($this->faker->postcode);
            $contact->setCity($this->faker->city);
            $contact->setAddress($this->faker->streetAddress.' '.$this->faker->buildingNumber);
            $contact->setPhone($this->faker->phoneNumber);
            $contact->setMessage($this->faker->text(800));

            if ($this->faker->boolean()) {
                $entity = new ContactReply();
                $entity->setMessage($this->faker->text);
                $entity->setRecipient($contact);

                /** @var User $user */
                $user = $this->getRandomReference(User::class, 'admin_users');
                $entity->setEditor($user);

                $contact->addContactReply($entity);

                $this->getManager()->persist($entity);
            }

            return $contact;
        });

        $manager->flush();
    }

    public function getDependencies()
    {
        return [UserFixture::class];
    }
}
