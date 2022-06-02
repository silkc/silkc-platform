<?php

namespace App\DataFixtures;

use App\Entity\TrainingSkill;
use App\Entity\User;
use App\Repository\SkillRepository;
use App\Repository\TrainingRepository;
use App\Repository\OccupationRepository;
use App\Repository\UserRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use App\Entity\Training;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class TrainingImportFixtures extends Fixture
{
    private $_occupationRepository;
    private $_trainingRepository;
    private $_skillRepository;
    private $_userRepository;
    private $_validator;
    private $_client;

    public function __construct(
        OccupationRepository $occupationRepository,
        TrainingRepository $trainingRepository,
        SkillRepository $skillRepository,
        UserRepository $userRepository,
        ValidatorInterface $validator,
        HttpClientInterface $client
    )
    {
        $this->_trainingRepository = $trainingRepository;
        $this->_skillRepository = $skillRepository;
        $this->_occupationRepository = $occupationRepository;
        $this->_userRepository = $userRepository;
        $this->_validator = $validator;
        $this->_client = $client;
    }

    public function load(ObjectManager $manager)
    {
        print "-- Beginning of the import of institutions" . PHP_EOL . PHP_EOL;
        $count = 0;

        $createdAt = new \DateTime('now');
        $code = random_int(100000, 999999);

        // Bundle to manage file and directories
        $finder = new Finder();
        $finder->in(__DIR__ . '/JSON');
        $finder->name('*.json');
        $finder->files();
        $finder->sortByName();

        $rate = $this->_get_rate();

        foreach( $finder as $file ){
            $jsonData = $file->getContents();
            $data = json_decode($jsonData);

            if (is_object($data) && property_exists($data, 'training') && is_array($data->training)) {
                $count++;
                print  PHP_EOL . "- Start of file processing {$file->getBasename()}" . PHP_EOL;

                foreach ($data->training as $k => $trainingData) {
                    print "Checking the data of entry no. {$k}" . PHP_EOL;
                    if (
                        !is_object($trainingData) ||
                        !property_exists($trainingData, 'institution_email') ||
                        !property_exists($trainingData, 'occupation_concept_uri') ||
                        !property_exists($trainingData, 'name') ||
                        empty($trainingData->institution_email) ||
                        empty($trainingData->occupation_concept_uri) ||
                        empty($trainingData->name)
                    ) {
                        print "Error --- wrong data format or missing institution_email, occupation_concept_uri or name" . PHP_EOL;
                        continue;
                    }

                    $error = false;

                    $institution = $this->_userRepository->findOneByEmailOrUsername($trainingData->institution_email);
                    if (!$institution) {
                        print  "ERROR --- No institution found for this email {$trainingData->institution_email}" . PHP_EOL;
                        continue;
                    }
                    $occupation = $this->_occupationRepository->findOneBy(['conceptUri' => $trainingData->occupation_concept_uri]);
                    if (!$occupation) {
                        print  "ERROR --- No occupation found for this concept URI {$trainingData->occupation_concept_uri}" . PHP_EOL;
                        continue;
                    }

                    try {
                        $training = new Training();
                        $training->setUser($institution);
                        $training->setCreator($institution);
                        $training->setOccupation($occupation);
                        $training->setName($trainingData->name);
                        if (property_exists($trainingData, 'location') && !empty($trainingData->location))
                            $training->setLocation($trainingData->location);
                        (property_exists($trainingData, 'language') && !empty($trainingData->language) && in_array(trim(strtolower($trainingData->language)), Training::getLanguages())) ?
                            $training->setLanguage(trim(strtolower($trainingData->language))) :
                            $training->setLanguage(Training::LANGUAGE_EN);
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
                        if (property_exists($trainingData, 'currency') && !empty($trainingData->currency))
                            $training->setCurrency($trainingData->currency);
                        if (property_exists($trainingData, 'start_at') && !empty($trainingData->start_at)) {
                            $startAt = \DateTime::createFromFormat('Y-m-d H:i:s', $trainingData->start_at);
                            if ($startAt && $startAt->format('Y-m-d H:i:s') === $trainingData->start_at)
                                $training->setStartAt($startAt);
                        }
                        if ($rate && $training->getPrice() !== null && $training->getPrice() > 0 && $training->getCurrency() === Training::CURRENCY_ZLOTY) {
                            $training->setEuroPrice(floatval(number_format($training->getPrice() / $rate, 2)));
                        } else if ($training->getCurrency() === Training::CURRENCY_EURO) {
                            $training->setEuroPrice($training->getPrice());
                        }
                        if (property_exists($trainingData, 'end_at') && !empty($trainingData->end_at)) {
                            $endAt = \DateTime::createFromFormat('Y-m-d H:i:s', $trainingData->end_at);
                            if ($endAt && $endAt->format('Y-m-d H:i:s') === $trainingData->end_at)
                                $training->setEndAt($endAt);
                        }
                        if (property_exists($trainingData, 'is_online') && !empty($trainingData->is_online))
                            $training->setIsOnline(filter_var($trainingData->is_online, FILTER_VALIDATE_BOOLEAN));
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

                        $errors = $this->_validator->validate($training);
                        if ($errors && count($errors) > 0) {
                            $errorString = (string) $errors;
                            print "ERROR --- An error occurred while check data before saving the training :  {$errorString}" . PHP_EOL;
                            continue;
                        }

                        // Validation par défaut de la formation
                        $training->setIsValidated(true);
                        $training->setValidatedAt($createdAt);

                        $manager->persist($training);
                        $manager->flush();

                        if (property_exists($trainingData, 'required_skills') && !empty($trainingData->required_skills)) {
                            $requiredSkills = is_array($trainingData->required_skills) ? $trainingData->required_skills : (array) $trainingData->required_skills;

                            if ($requiredSkills && count($requiredSkills) > 0) {
                                foreach ($requiredSkills as $key => $concept_uri) {
                                    $concept_uri = preg_replace('#(\\\)#', '', $concept_uri);
                                    $skill = $this->_skillRepository->findOneBy(['conceptUri' => $concept_uri]);
                                    if (!$skill) {
                                        print "ERROR --- An error occurred while associating the skill to training {$concept_uri}" . PHP_EOL;
                                        continue;
                                    }

                                    $trainingSkill = new TrainingSkill();
                                    $trainingSkill->setTraining($training);
                                    $trainingSkill->setSkill($skill);
                                    $trainingSkill->setIsRequired(true);
                                    $trainingSkill->setIsToAcquire(false);
                                    $training->addTrainingSkill($trainingSkill);
                                    $manager->persist($trainingSkill);
                                }
                            }
                        }
                        if (property_exists($trainingData, 'acquired_skills') && !empty($trainingData->acquired_skills)) {
                            $acquiredSkills = is_array($trainingData->acquired_skills) ? $trainingData->acquired_skills : (array) $trainingData->acquired_skills;

                            if ($acquiredSkills && count($acquiredSkills) > 0) {
                                foreach ($acquiredSkills as $key => $concept_uri) {
                                    $concept_uri = preg_replace('#(\\\)#', '', $concept_uri);
                                    $skill = $this->_skillRepository->findOneBy(['conceptUri' => $concept_uri]);
                                    if (!$skill) {
                                        print "ERROR --- An error occurred while associating the skill to training {$concept_uri}" . PHP_EOL;
                                        continue;
                                    }

                                    $trainingSkill = new TrainingSkill();
                                    $trainingSkill->setTraining($training);
                                    $trainingSkill->setSkill($skill);
                                    $trainingSkill->setIsRequired(false);
                                    $trainingSkill->setIsToAcquire(true);
                                    $training->addTrainingSkill($trainingSkill);

                                    $manager->persist($trainingSkill);
                                }
                            }
                        }
                    } catch(\Throwable $e) {
                        print "ERROR --- An error occurred while saving the training in the database :  {$e->getMessage()}" . PHP_EOL;
                        $error = true;
                    }

                    if (!$error)
                        print "The training '{$trainingData->name}' has been added" . PHP_EOL;
                }
            }
        }

        if ($count === 0)
            print "No data file containing the key 'training' was found" . PHP_EOL;
    }

    protected function _get_rate():float
    {
        $rate = null;

        try {
            $response = $this->_client->request(
                'GET',
                'http://api.nbp.pl/api/exchangerates/rates/a/eur?format=json'
            );

            $data = $response->toArray();

            if (!$data || !is_array($data) || !array_key_exists('rates', $data) || !is_array($data['rates']))
                throw new \Exception('Get currency rate error');

            $current = current($data['rates']);
            $rate = (is_array($current) && array_key_exists('mid', $current)) ?
                $current['mid'] :
                null;

            if (!$rate)
                throw new \Exception('Currency rate empty');
        } catch(\Throwable $e) {

        }

        return $rate;
    }
}
