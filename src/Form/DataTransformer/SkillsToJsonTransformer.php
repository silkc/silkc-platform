<?php

namespace App\Form\DataTransformer;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class SkillsToJsonTransformer implements DataTransformerInterface
{
    public function reverseTransform($skills)
    {
        /*if (is_string($propositions) && !empty($propositions) && json_decode($propositions)) {
            return json_decode($propositions);
        }*/

        /*if (null === $propositions) {
            $privateErrorMessage = sprintf('An issue with number "%s" does not exist!', $issueNumber);
            $publicErrorMessage = 'The given "{{ value }}" value is not a valid issue number.';

            $failure = new TransformationFailedException($privateErrorMessage);
            $failure->setInvalidMessage($publicErrorMessage, [
                '{{ value }}' => $issueNumber,
            ]);

            throw $failure;
        }*/

        return NULL;
    }

    public function transform($value)
    {
        //dd($value);
        /*if (isset($value) && is_array($value) && !empty($value))
            return json_encode($value);*/

        return $value;
    }
}