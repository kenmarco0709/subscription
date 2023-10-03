<?php

namespace App\Entity;

use App\Entity\Base\BaseEntity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToStringTransformer;

/**
 * @ORM\Entity(repositoryClass="App\Repository\BranchVariableRepository")
 * @ORM\Table(name="branch_variable")
 * @ORM\HasLifecycleCallbacks()
 */

class BranchVariableEntity extends BaseEntity
{
    /**
     * @ORM\Column(type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(name="description", type="string")
     */
    protected $description;

    /**
     * @ORM\Column(name="branch_variable_value", type="string")
     */
    protected $branchVariableValue;

    /**
     * @ORM\ManyToOne(targetEntity="BranchEntity", inversedBy="branchVariables")
     * @ORM\JoinColumn(name="branch_id", referencedColumnName="id", nullable=true)
     */
    protected $branch;




    public function __construct($data = null)
    {
    }

    /*--------------------------------------------------------------------------------------------------------*/
    /*					User Defined Setters and Getters													  */
    /*--------------------------------------------------------------------------------------------------------*/

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getBranchVariableValue(): ?string
    {
        return $this->branchVariableValue;
    }

    public function setBranchVariableValue(string $branchVariableValue): self
    {
        $this->branchVariableValue = $branchVariableValue;

        return $this;
    }

    public function getBranch(): ?BranchEntity
    {
        return $this->branch;
    }

    public function setBranch(?BranchEntity $branch): self
    {
        $this->branch = $branch;

        return $this;
    }

    

    public function setIsDeleted(?bool $isDeleted): self
    {
        $this->isDeleted = $isDeleted;

        return $this;
    }


}
