<?php

namespace App\DataFixtures;

use App\Entity\Training;
use App\Entity\TrainingSkill;
use App\Entity\UserOccupation;
use App\Repository\OccupationRepository;
use App\Repository\SkillRepository;
use App\Repository\TrainingRepository;
use App\Repository\UserRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use App\Entity\User;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserImportFixtures extends Fixture
{
    private $_passwordEncoder;
    private $_userRepository;
    private $_trainingRepository;
    private $_skillRepository;
    private $_occupationRepository;
    private $_validator;

    public function __construct(
        UserPasswordEncoderInterface $passwordEncoder,
        UserRepository $userRepository,
        TrainingRepository $trainingRepository,
        SkillRepository $skillRepository,
        OccupationRepository $occupationRepository,
        ValidatorInterface $validator
    )
    {
        $this->_passwordEncoder = $passwordEncoder;
        $this->_userRepository = $userRepository;
        $this->_trainingRepository = $trainingRepository;
        $this->_skillRepository = $skillRepository;
        $this->_occupationRepository = $occupationRepository;
        $this->_validator = $validator;
    }

    public function load(ObjectManager $manager)
    {
        print "-- Beginning of the import of users" . PHP_EOL . PHP_EOL;
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

            if (is_object($data) && property_exists($data, 'user') && is_array($data->user)) {
                $count++;
                print  PHP_EOL . "- Start of file processing {$file->getBasename()}" . PHP_EOL;

                foreach ($data->user as $k => $userData) {
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
                        $user = new User();
                        $user->setUsername($userData->username);
                        $user->setEmail($userData->email);
                        $user->setAddress($userData->address);
                        $user->setHomepage((property_exists($userData, 'homepage') && !empty($userData->homepage)) ? $userData->homepage : null);
                        if (property_exists($userData, 'dateOfBirth') && !empty($userData->dateOfBirth))
                            $user->setDateOfBirth(new \DateTime($userData->dateOfBirth));
                        $user->setIsValidated(true);
                        $user->setCode($code);
                        $password = $this->_passwordEncoder->encodePassword($user, $code);
                        $apiToken = base64_encode(sha1($createdAt->format('Y-m-d H:i:s').$password, true));
                        $user->setTokenCreatedAt($createdAt);
                        $user->setCreatedAt($createdAt);
                        $user->setApiToken($apiToken);
                        $user->setRoles([User::ROLE_USER]);
                        $user->setPassword($password);

                        if (
                            property_exists($userData, 'experience') &&
                            !empty($userData->experience) &&
                            preg_match('#^.*?([\d]{1,})$#', $userData->experience, $matches)
                        )
                            $user->setProfessionalExperience(intval($matches[1]));

                        $errors = $this->_validator->validate($user);
                        if ($errors && count($errors) > 0) {
                            $errorString = (string) $errors;
                            print "ERROR --- An error occurred while check data before saving the user :  {$errorString}" . PHP_EOL;
                            continue;
                        }

                        $manager->persist($user);

                        if (
                            property_exists($userData, 'previousJob') && !empty($userData->previousJob) ||
                            property_exists($userData, 'previousJobs') && !empty($userData->previousJobs)
                        ) {
                            $previousOccupations = (property_exists($userData, 'previousJob') && !empty($userData->previousJob)) ?
                                (array) $userData->previousJob :
                                (array) $userData->previousJobs;

                            if ($previousOccupations && count($previousOccupations) > 0) {
                                foreach ($previousOccupations as $concept_uri) {
                                    $occupation = $this->_occupationRepository->findOneBy(['conceptUri' => $concept_uri]);
                                    if (!$occupation) {
                                        print "ERROR --- An error occurred while associating the previous job {$concept_uri} :  {$e->getMessage()}" . PHP_EOL;
                                        continue;
                                    }

                                    $userOccupation = new UserOccupation();
                                    $userOccupation->setUser($user);
                                    $userOccupation->setOccupation($occupation);
                                    $userOccupation->setIsPrevious(true);
                                    $user->addUserOccupation($userOccupation);

                                    $manager->persist($userOccupation);
                                }
                            }
                        }

                        if (
                            property_exists($userData, 'currentJob') && !empty($userData->currentJob) ||
                            property_exists($userData, 'currentJobs') && !empty($userData->currentJobs)
                        ) {
                            $currentOccupations = (property_exists($userData, 'currentJob') && !empty($userData->currentJob)) ?
                                (array) $userData->currentJob :
                                (array) $userData->currentJobs;

                            if ($currentOccupations && count($currentOccupations) > 0) {
                                foreach ($currentOccupations as $concept_uri) {
                                    $occupation = $this->_occupationRepository->findOneBy(['conceptUri' => $concept_uri]);
                                    if (!$occupation) {
                                        print "ERROR --- An error occurred while associating the current job {$concept_uri} :  {$e->getMessage()}" . PHP_EOL;
                                        continue;
                                    }

                                    $userOccupation = new UserOccupation();
                                    $userOccupation->setUser($user);
                                    $userOccupation->setOccupation($occupation);
                                    $userOccupation->setIsCurrent(true);
                                    $user->addUserOccupation($userOccupation);

                                    $manager->persist($userOccupation);
                                }
                            }
                        }

                        if (
                            property_exists($userData, 'trainings') && !empty($userData->trainings) ||
                            (
                                property_exists($userData, 'previousTrainingInstitution') && !empty($userData->previousTrainingInstitution) &&
                                property_exists($userData, 'previousTrainingTitle') && !empty($userData->previousTrainingTitle) &&
                                property_exists($userData, 'previousTrainingDuration') && !empty($userData->previousTrainingDuration)
                            )
                        ) {
                            $trainings = (property_exists($userData, 'trainings') && !empty($userData->trainings)) ?
                                (array) $userData->trainings :
                                [
                                    (object) [
                                        "institution_email" => $userData->previousTrainingInstitution,
                                        "location" => (property_exists($userData, 'previousTrainingLocation') && !empty($userData->previousTrainingLocation)) ?
                                            $userData->previousTrainingLocation :
                                            null,
                                        "name" => $userData->previousTrainingTitle,
                                        "language" => (property_exists($userData, 'previousTrainingLanguage') && !empty($userData->previousTrainingLanguage)) ?
                                            $userData->previousTrainingLanguage :
                                            Training::LANGUAGE_EN,
                                        "url" => (property_exists($userData, 'previousTrainingURL') && !empty($userData->previousTrainingURL)) ?
                                            $userData->previousTrainingURL :
                                            null,
                                        "price" => (property_exists($userData, 'previousTrainingCost') && !empty($userData->previousTrainingCost)) ?
                                            $userData->previousTrainingCost :
                                            null,
                                        "currency" => (property_exists($userData, 'previousTrainingCurrency') && !empty($userData->previousTrainingCurrency)) ?
                                            $userData->previousTrainingCost :
                                            null,
                                        "start_at" => (property_exists($userData, 'previousTrainingYear') && !empty($userData->previousTrainingYear)) ?
                                            $userData->previousTrainingYear :
                                            null,
                                        "duration_value" => (
                                            property_exists($userData, 'previousTrainingDuration') &&
                                            !empty($userData->previousTrainingDuration) &&
                                            preg_match('#^.*?([\d]{1,}\+?)$#', $userData->previousTrainingDuration, $matches)
                                        ) ?
                                            intval($matches[1]) :
                                            null,
                                        "duration_unity" => "hours",
                                        "skills" => (property_exists($userData, 'previousTrainingSkill') && !empty($userData->previousTrainingSkill)) ?
                                            (array) $userData->previousTrainingSkill :
                                            null
                                    ]
                                ];

                            if ($trainings && count($trainings) > 0) {
                                foreach ($trainings as $trainingData) {
                                    if (
                                        !is_object($trainingData) ||
                                        !property_exists($trainingData, 'institution_email') ||
                                        !property_exists($trainingData, 'name') ||
                                        empty($trainingData->institution_email) ||
                                        empty($trainingData->name)
                                    ) {
                                        print "Error --- wrong data format or missing institution_email or name" . PHP_EOL;
                                        continue;
                                    }

                                    $error = false;

                                    $institution = $this->_userRepository->findOneByEmailOrUsername($trainingData->institution_email);
                                    if (!$institution && !empty($trainingData->url)) {
                                        try {
                                            $valid_url = preg_match('#(https?://)([\w\.]+).*?$#', $trainingData->url, $matches);
                                            if (!$valid_url) {
                                                print "ERROR --- An error occurred while check data before saving the institution by url :  {$errorString}" . PHP_EOL;
                                                continue;
                                            }
                                            $institution_email = $matches[2] . '@silkc-platform.org';
                                            $institution = $this->_userRepository->findOneByEmailOrUsername($institution_email);
                                            if (!$institution) {
                                                $code = random_int(100000, 999999);
                                                $institution = new User();
                                                $institution->setUsername($trainingData->institution_email);
                                                $institution->setEmail($institution_email);
                                                $institution->setIsValidated(true);
                                                $institution->setCode($code);
                                                $password = $this->_passwordEncoder->encodePassword($institution, $code);
                                                $apiToken = base64_encode(sha1($createdAt->format('Y-m-d H:i:s') . $password, true));
                                                $institution->setTokenCreatedAt($createdAt);
                                                $institution->setCreatedAt($createdAt);
                                                $institution->setApiToken($apiToken);
                                                $institution->setRoles([User::ROLE_INSTITUTION]);
                                                $institution->setPassword($password);

                                                $errors = $this->_validator->validate($institution);
                                                if ($errors && count($errors) > 0) {
                                                    $errorString = (string)$errors;
                                                    print "ERROR --- An error occurred while check data before saving the institution :  {$errorString}" . PHP_EOL;
                                                    continue;
                                                }

                                                $manager->persist($institution);
                                                $manager->flush();
                                            }
                                        } catch(\Throwable $e) {
                                            print "ERROR --- An error occurred while saving the institution in the database :  {$e->getMessage()}" . PHP_EOL;
                                            $error = true;
                                        }
                                    }

                                    try {
                                        $training = new Training();
                                        $training->setUser($institution);
                                        $training->setCreator($institution);
                                        if (property_exists($trainingData, 'occupation_concept_uri') && !empty($trainingData->occupation_concept_uri)) {
                                            $occupation = $this->_occupationRepository->findOneBy(['conceptUri' => $trainingData->occupation_concept_uri]);
                                            if ($occupation)
                                                $training->setOccupation($occupation);
                                        }
                                        $training->setName($trainingData->name);
                                        if (property_exists($trainingData, 'location') && !empty($trainingData->location))
                                            $training->setLocation($trainingData->location);
                                        if (property_exists($trainingData, 'longitude') && !empty($trainingData->longitude))
                                            $training->setLongitude($trainingData->longitude);
                                        if (property_exists($trainingData, 'latitude') && !empty($trainingData->latitude))
                                            $training->setLatitude($trainingData->latitude);
                                        if (property_exists($trainingData, 'duration_value') && !empty($trainingData->duration_value))
                                            $training->setDurationValue(intval($trainingData->duration_value));
                                        if (property_exists($trainingData, 'duration_unity') && !empty($trainingData->duration_unity))
                                            $training->setDurationUnity($trainingData->duration_unity);
                                        if (property_exists($trainingData, 'duration_details') && !empty($trainingData->duration_details))
                                            $training->setDurationDetails($trainingData->duration_details);
                                        if (property_exists($trainingData, 'description') && !empty($trainingData->description))
                                            $training->setDescription($trainingData->description);
                                        if (property_exists($trainingData, 'url') && !empty($trainingData->url))
                                            $training->setUrl($trainingData->url);
                                        if (property_exists($trainingData, 'price') && !empty($trainingData->price))
                                            $training->setPrice(intval($trainingData->price));
                                        if (property_exists($trainingData, 'language') && !empty($trainingData->language))
                                            $training->setLanguage($trainingData->language);
                                        if (property_exists($trainingData, 'currency') && !empty($trainingData->currency))
                                            $training->setCurrency($trainingData->currency);
                                        if (property_exists($trainingData, 'start_at') && !empty($trainingData->start_at)) {
                                            $startAt = \DateTime::createFromFormat('Y-m-d H:i:s', $trainingData->start_at);
                                            if ($startAt && $startAt->format('Y-m-d H:i:s') === $trainingData->start_at)
                                                $training->setStartAt($startAt);
                                        }
                                        if (property_exists($trainingData, 'end_at') && !empty($trainingData->end_at)) {
                                            $endAt = \DateTime::createFromFormat('Y-m-d H:i:s', $trainingData->end_at);
                                            if ($endAt && $endAt->format('Y-m-d H:i:s') === $trainingData->end_at)
                                                $training->setEndAt($endAt);
                                        }
                                        if (property_exists($trainingData, 'is_online') && !empty($trainingData->is_online))
                                            $training->setIsOnline(filter_var($trainingData->is_online, FILTER_VALIDATE_BOOLEAN));
                                        else if (property_exists($trainingData, 'location') && $trainingData->location == 'online')
                                            $training->setIsOnline(true);
                                        else
                                            $training->setIsOnline(false);
                                        if (property_exists($trainingData, 'is_online_monitored') && !empty($trainingData->is_online_monitored))
                                            $training->setIsOnlineMonitored(filter_var($trainingData->is_online_monitored, FILTER_VALIDATE_BOOLEAN));
                                        else
                                            $training->setIsOnlineMonitored(false);
                                        if (property_exists($trainingData, 'is_presential') && !empty($trainingData->is_presential))
                                            $training->setIsPresential(filter_var($trainingData->is_presential, FILTER_VALIDATE_BOOLEAN));
                                        else
                                            $training->setIsPresential(false);

                                        if (property_exists($trainingData, 'skills') && !empty($trainingData->skills)) {
                                            $skills = (array) $trainingData->skills;

                                            if ($skills && count($skills) > 0) {
                                                foreach ($skills as $concept_uri) {
                                                    $skill = $this->_skillRepository->findOneBy(['conceptUri' => $concept_uri]);
                                                    if (!$skill) {
                                                        print "ERROR --- An error occurred while associating the skill to training {$concept_uri} :  {$e->getMessage()}" . PHP_EOL;
                                                        continue;
                                                    }

                                                    $trainingSkill = new TrainingSkill();
                                                    $trainingSkill->setTraining($training);
                                                    $trainingSkill->setSkill($skill);
                                                    $trainingSkill->setIsRequired(true);
                                                    $training->addTrainingSkill($trainingSkill);

                                                    $manager->persist($trainingSkill);
                                                }
                                            }
                                        }

                                        $errors = $this->_validator->validate($training);
                                        if ($errors && count($errors) > 0) {
                                            $errorString = (string) $errors;
                                            print "ERROR --- An error occurred while check data before saving the training :  {$errorString}" . PHP_EOL;
                                            continue;
                                        }

                                        $manager->persist($training);

                                        $user->addTraining($training);

                                    } catch(\Throwable $e) {
                                        print "ERROR --- An error occurred while saving the training in the database :  {$e->getMessage()}" . PHP_EOL;
                                        $error = true;
                                    }

                                    if (!$error)
                                        print "The training '{$trainingData->name}' has been added" . PHP_EOL;

                                    $manager->persist($training);
                                }
                            }
                        }

                        $manager->persist($user);
                        $manager->flush();
                    } catch(\Throwable $e) {
                        print "ERROR --- An error occurred while saving the institution in the database :  {$e->getMessage()}" . PHP_EOL;
                        $error = true;
                    }

                    if (!$error)
                        print "The user '{$userData->username}' has been added" . PHP_EOL;
                }
            }
        }

        if ($count === 0)
            print "No data file containing the key 'user' was found" . PHP_EOL;
    }
}
