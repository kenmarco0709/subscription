<?php

namespace App\Form\DataTransformer;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class DatetimeTransformer implements DataTransformerInterface
{

    public function __construct()
    {
       
    }

    /**
     * @param  \DateTime|null $dateTime
     * @return string|null
     */
    public function transform($dateTime) {

        if (null === $dateTime) {
            return null;
        }


        return $dateTime->format('m/d/Y');
    }

    /**
     * @param  string $value
     * @return string|null
     */
    public function reverseTransform($value)
    {

        if ( (null === $value) || empty($value) ) {
            return null;
        }
        return new \DateTime( $value );

    }
}