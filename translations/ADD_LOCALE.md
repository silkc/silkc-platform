# Add

**On the resource files download page ESCO : https://ec.europa.eu/esco/portal/download**

**Ajouter les filtres suivants :**
- Version : 1.0.8
- Langue : [la langue à ajouter]
- Type : CSV

**Select the "Occupations" and "Competences/skills" files, then click on the "Prepare your set" button to download the files**

**On the database administration tool, add two temporary tables**

    DROP TABLE IF EXISTS `occupation_tmp`;
    CREATE TABLE IF NOT EXISTS `occupation_tmp` (
      `conceptType` varchar(255) NOT NULL,
      `conceptUri` text NOT NULL,
      `iscoGroup` text NOT NULL,
      `preferredLabel` text NOT NULL,
      `altLabels` text NOT NULL,
      `hiddenLabels` text NOT NULL,
      `status` text NOT NULL,
      `modifiedDate` text NOT NULL,
      `regulatedProfessionNote` text NOT NULL,
      `scopeNote` text NOT NULL,
      `definition` text NOT NULL,
      `inScheme` text NOT NULL,
      `description` text NOT NULL,
      `code` text NOT NULL
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8;

    DROP TABLE IF EXISTS `skill_tmp`;
    CREATE TABLE IF NOT EXISTS `skill_tmp` (
      `conceptType` varchar(255) NOT NULL,
      `conceptUri` text NOT NULL,
      `skillType` text NOT NULL,
      `reuseLevel` text NOT NULL,
      `preferredLabel` text NOT NULL,
      `altLabels` text NOT NULL,
      `hiddenLabels` text NOT NULL,
      `status` text NOT NULL,
      `modifiedDate` text NOT NULL,
      `scopeNote` text NOT NULL,
      `definition` text NOT NULL,
      `inScheme` text NOT NULL,
      `description` text NOT NULL
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8;


**Import data from file occupations_[locale].csv in the table occupations_tmp, and skills_[locale].csv in the table skills_tmp**

**Run the following query to transfer the data to the translation tables, replacing [locale] with the code of the language to add.**

    INSERT INTO `occupation_translation` (`occupation_id`, `locale`, `preferred_label`, `alt_labels`, `hidden_labels`, `definition`, `description`)
    SELECT
        Occupation.id,
        '[locale]',
        TMP.preferredLabel,
        TMP.altLabels,
        TMP.hiddenLabels,
        TMP.definition,
        TMP.description
    FROM `occupation_tmp` AS TMP
    LEFT JOIN `occupation` AS Occupation ON Occupation.concept_uri = TMP.conceptUri;

    INSERT INTO `skill_translation` (`skill_id`, `locale`, `preffered_label`, `alt_labels`, `hidden_labels`, `definition`, `description`)
    SELECT
        Skill.id,
            'en',
            TMP.preferredLabel,
            TMP.altLabels,
            TMP.hiddenLabels,
            TMP.definition,
            TMP.description
    FROM `skill_tmp` AS TMP
    LEFT JOIN `skill` AS Skill ON Skill.concept_uri = TMP.conceptUri;

**Create the messages. [Locale] .yaml translation file in the translations folder of the project directory, and translate all the values ​​from the messages.en.yaml file into it**

**In the files templates/front/elements/header.html.twig and templates/admin/elements/header.html.twig, add an option to the drop-down list of the choice of languages**

    <a class="dropdown-item link-langue" href="{{ path(current_path, route_params|merge({_locale: '[locale]'})) }}"><img src="{{asset('build/images/flags/[locale].png')}}" alt=""> [locale]</a>
