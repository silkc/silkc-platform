<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Repository\TrainingRepository;
use App\Repository\OccupationRepository;
use App\Repository\UserRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use App\Entity\Training;

class TrainingImportFixtures extends Fixture
{
    private $_occupationRepository;
    private $_trainingRepository;
    private $_userRepository;

    public function __construct(OccupationRepository $occupationRepository, TrainingRepository $trainingRepository, UserRepository $userRepository)
    {
        $this->_trainingRepository = $trainingRepository;
        $this->_occupationRepository = $occupationRepository;
        $this->_userRepository = $userRepository;
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

                    $training = new Training();
                    $training->setUser($institution);
                    $training->setCreator($institution);
                    $training->setOccupation($occupation);
                    $training->setName($trainingData->name);
                    if (property_exists($trainingData, 'location') && !empty($trainingData->location))
                        $training->setLocation($trainingData->location);
                    if (property_exists($trainingData, 'duration') && !empty($trainingData->duration))
                        $training->setDuration($trainingData->duration);
                    if (property_exists($trainingData, 'description') && !empty($trainingData->description))
                        $training->setDescription($trainingData->description);
                    if (property_exists($trainingData, 'url') && !empty($trainingData->url))
                        $training->setUrl($trainingData->url);
                    if (property_exists($trainingData, 'price') && !empty($trainingData->price))
                        $training->setPrice($trainingData->price);
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
                    if (property_exists($trainingData, 'is_online_monitored') && !empty($trainingData->is_online_monitored))
                        $training->setIsOnlineMonitored(filter_var($trainingData->is_online_monitored, FILTER_VALIDATE_BOOLEAN));
                    if (property_exists($trainingData, 'is_presential') && !empty($trainingData->is_presential))
                        $training->setIsPresential(filter_var($trainingData->is_presential, FILTER_VALIDATE_BOOLEAN));

                    $manager->persist($training);

                    try {
                        $manager->flush();
                    } catch(\Throwable $e) {
                        print "ERROR --- An error occurred while saving the institution in the database :  {$e->getMessage()}" . PHP_EOL;
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
}
