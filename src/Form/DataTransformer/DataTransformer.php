<?php

namespace App\Form\DataTransformer;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class DataTransformer implements DataTransformerInterface
{
    private $manager;
    private $class;
    private $idIsBase64;
    private $primaryKey;

    public function __construct(ObjectManager $manager, $class, $idIsBase64=true, $primaryKey=null)
    {
        $this->manager = $manager;
        $this->class = $class;
        $this->idIsBase64 = $idIsBase64;
        $this->primaryKey = $primaryKey;
    }

    public function getPrimaryKey(){
        return $this->primaryKey;
    }

    /**
     * Transforms an object (issue) to a string (number).
     *
     * @param  Issue|null $issue
     * @return string
     */

    public function transform($class)
    {

        $method = null;
        if (null === $class) {
            return '';
        }

        if($this->primaryKey != null){
            
            return $this->primaryKey;
        }

        return $class->getId();
    }

    /**
     * Transforms a string (number) to an object (issue).
     *
     * @param  string $issueNumber
     * @return Issue|null
     * @throws TransformationFailedException if object (issue) is not found.
     */
    public function reverseTransform($id)
    {

            if (!$id) {
            return;
        }

        if (null === $id) {
            throw new TransformationFailedException(sprintf(
                'An issue with number "%s" does not exist!',
                $id
            ));
        } else {

            return $this->manager
                ->getRepository($this->class)
                ->find($this->idIsBase64 ? base64_decode($id) : $id);
        }
    }
}