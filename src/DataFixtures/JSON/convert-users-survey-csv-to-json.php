<?php
/*
 * License terms: see /license.txt
 */
/**
 * This script transforms a CSV file with the proper format to a JSON file ready for import in the application.
 * CSV format should have a first line of titles as follows:
 * dateOfBirth,Country of residency,City of residency,address,language,Previous job,previousJob,Current job,currentJob,experience,previousTrainingTitle,Skills acquired,previousTrainingSkill,previousTrainingInstitution,previousTrainingLanguage,previousTrainingURL,previousTrainingDuration,previousTrainingYear,previousTrainingCost,previousTrainingLocation
 */
$filename = __DIR__.'/users-survey.csv';
$csv = fopen($filename, 'ro');
$i = 0;
$titles = [];
$users = [];
// Supported languages
$languages = [
    'English' => 'en',
    'French' => 'fr',
    'Polish' => 'pl',
    'Italian' => 'it',
    'Dutch' => 'nl',
    'Spanish' => 'es',
];
if(false !== $csv) {
    while ($line = fgetcsv($csv, 5000, ',', '"')) {
        if (0 == $i) {
            // Titles line
            foreach ($line as $key => $value) {
                $titles[$key] = trim($value);
            }
            $titles = array_flip($titles);
        } else {
            $trainingLocation = trim($line[$titles['previousTrainingLocation']]);
            if ('online' !== strtolower($trainingLocation)) {
                $trainingLocation = $trainingLocation.', '.trim($line[$titles['Country of residency']]);
            }
            $language = _nullIfEmpty($line[$titles['language']]);
            if (!empty($languages[$language])) {
                $language = $languages[$language];
            }
            $trainingLanguage = _nullIfEmpty($line[$titles['previousTrainingLanguage']]);
            if (!empty($languages[$trainingLanguage])) {
                $trainingLanguage = $languages[$trainingLanguage];
            }
            $users[] = [
                'username' => 'survey-user-'.$i,
                'email' => 'survey-user-'.$i.'@silkc-platform.org',
                'dateOfBirth' => $line[$titles['dateOfBirth']],
                'address' => $line[$titles['address']],
                'language' => $language,
                'experience' => _nullIfEmpty($line[$titles['experience']]),
                'previousJob' => _nullIfEmpty($line[$titles['previousJob']]),
                'currentJob' => _nullIfEmpty($line[$titles['currentJob']]),
                'previousTrainingInstitution' => _nullIfEmpty($line[$titles['previousTrainingInstitution']]),
                'previousTrainingLocation' => _nullIfEmpty($trainingLocation),
                'previousTrainingTitle' => _nullIfEmpty($line[$titles['previousTrainingTitle']]),
                'previousTrainingLanguage' => $trainingLanguage,
                'previousTrainingURL' => _nullIfEmpty($line[$titles['previousTrainingURL']]),
                'previousTrainingCost' => _nullIfEmpty($line[$titles['previousTrainingCost']]),
                'previousTrainingYear' => _nullIfEmpty($line[$titles['previousTrainingYear']]),
                'previousTrainingDuration' => _nullIfEmpty($line[$titles['previousTrainingDuration']]),
                'previousTrainingSkill' => _nullIfEmpty($line[$titles['previousTrainingSkill']])
            ];
        }
        $i++;
    }
    fclose($csv);
}
//print_r($titles);
//print_r(json_encode($users[100]));
$json = json_encode(['user' => $users]);
file_put_contents('user_import.json', $json);

/**
 * Return null if the string is empty or is the *string* 'null'
 * @param $param
 * @return string|null
 */
function _nullIfEmpty($param) {
    if (empty(trim($param)) or 'null' == trim($param)) {
        return null;
    }
    return trim($param);
}