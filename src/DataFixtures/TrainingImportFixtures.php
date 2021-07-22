<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use App\Entity\User;

class TrainingImportFixtures extends Fixture
{
    private $_passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->_passwordEncoder = $passwordEncoder;
    }

    public function load(ObjectManager $manager)
    {
        $createdAt = new \DateTime('now');
        $code = random_int(100000, 999999);

        // Bundle to manage file and directories
        $finder = new Finder();
        $finder->in(__DIR__ . '/JSON');
        $finder->name('*.json');
        $finder->files();
        $finder->sortByName();

        foreach( $finder as $file ){
            print "Importing: {$file->getBasename()} " . PHP_EOL;

            $jsonData = $file->getContents();
            $data = json_decode($jsonData);

            if (is_object($data) && property_exists($data, 'institution') && is_array($data->institution)) {
                foreach ($data->institution as $k => $userData) {

                    if (
                        !is_object($userData) ||
                        !property_exists($userData, 'username') ||
                        !property_exists($userData, 'email')
                    )
                        continue;

                    $institution = new User();
                    $institution->setUsername($userData->username);
                    $institution->setEmail($userData->email);
                    $institution->setAddress($userData->address);
                    $institution->setHomepage($userData->homepage);
                    if ($userData->dateOfBirth)
                        $institution->setDateOfBirth(new \DateTime($userData->dateOfBirth));
                    $institution->setIsValidated(true);
                    $institution->setCode($code);
                    $password = $this->_passwordEncoder->encodePassword($institution, 'institution');
                    $apiToken = base64_encode(sha1($createdAt->format('Y-m-d H:i:s').$password, true));
                    $institution->setTokenCreatedAt($createdAt);
                    $institution->setCreatedAt($createdAt);
                    $institution->setApiToken($apiToken);
                    $institution->setRoles([User::ROLE_INSTITUTION]);
                    $institution->setPassword($password);

                    $manager->persist($institution);
                    $manager->flush();
                }
            }
        }
    }
}
