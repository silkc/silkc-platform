<?php

namespace App\DataFixtures;

use App\Repository\UserRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use App\Entity\User;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class InstitutionImportFixtures extends Fixture
{
    private $_passwordEncoder;
    private $_userRepository;
    private $_validator;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder, UserRepository $userRepository, ValidatorInterface $validator)
    {
        $this->_passwordEncoder = $passwordEncoder;
        $this->_userRepository = $userRepository;
        $this->_validator = $validator;
    }

    public function load(ObjectManager $manager)
    {
        print "-- Beginning of the import of institutions" . PHP_EOL . PHP_EOL;
        $count = 0;

        $createdAt = new \DateTime('now');

        // Bundle to manage file and directories
        $finder = new Finder();
        $finder->in(__DIR__ . '/JSON');
        $finder->name('*.json');
        $finder->files();
        $finder->sortByName();

        foreach( $finder as $file ){
            $jsonData = $file->getContents();
            $data = json_decode($jsonData);

            if (is_object($data) && property_exists($data, 'institution') && is_array($data->institution)) {
                $count++;
                print  PHP_EOL . "- Start of file processing {$file->getBasename()}" . PHP_EOL;

                foreach ($data->institution as $k => $userData) {
                    $code = random_int(100000, 999999);

                    print "Checking the data of entry no. {$k}" . PHP_EOL;
                    if (
                        !is_object($userData) ||
                        !property_exists($userData, 'username') ||
                        !property_exists($userData, 'email') ||
                        empty($userData->username) ||
                        empty($userData->email)
                    ) {
                        print "Error --- wrong data format or missing unique identifier (username or email)" . PHP_EOL;
                        continue;
                    }

                    $error = false;
                    $existingUserByEmail = $this->_userRepository->findOneBy(['email' => $userData->email]);
                    $existingUserByUsername = $this->_userRepository->findOneBy(['username' => $userData->username]);
                    if ($existingUserByEmail) {
                        print "Error --- Email already exists {$userData->email}" . PHP_EOL;
                        continue;
                    }
                    if ($existingUserByUsername) {
                        print "Error --- Username already exists {$userData->username}" . PHP_EOL;
                        continue;
                    }

                    try {
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

                        $errors = $this->_validator->validate($institution);
                        if ($errors && count($errors) > 0) {
                            $errorString = (string) $errors;
                            print "ERROR --- An error occurred while check data before saving the institution :  {$errorString}" . PHP_EOL;
                            continue;
                        }

                        $manager->persist($institution);
                        $manager->flush();
                    } catch(\Throwable $e) {
                        print "ERROR --- An error occurred while saving the institution in the database :  {$e->getMessage()}" . PHP_EOL;
                        $error = true;
                    }

                    if (!$error)
                        print "The institution '{$userData->username}' has been added" . PHP_EOL;
                }
            }
        }

        if ($count === 0)
            print "No data file containing the key 'institution' was found" . PHP_EOL;
    }
}
