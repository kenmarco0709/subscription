<?php

namespace App\Entity;

use App\Entity\Base\BaseEntity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToStringTransformer;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ExpenseRepository")
 * @ORM\Table(name="expense")
 * @ORM\HasLifecycleCallbacks()
 */

class ExpenseEntity extends BaseEntity
{
    /**
     * @ORM\Column(type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(name="description", type="string", nullable=true)
     */
    protected $description;

    /**
     * @ORM\Column(name="amount", type="decimal", precision=12, scale=2)
     */
    protected $amount;

    /**
     * @ORM\Column(name="expense_date", type="datetime", nullable=true)
     */
    protected $expenseDate;
   
     /**
     * @ORM\ManyToOne(targetEntity="BranchEntity", inversedBy="expenses")
     * @ORM\JoinColumn(name="branch_id", referencedColumnName="id", nullable=true)
     */
    protected $branch;

    /**
     * @ORM\ManyToOne(targetEntity="ExpenseTypeEntity", inversedBy="expenses")
     * @ORM\JoinColumn(name="expense_type_id", referencedColumnName="id", nullable=true)
     */
    protected $expenseType;


   
   
    public function __construct()
    {
        $this->expenseAccounts = new ArrayCollection();
    }

    
    /*--------------------------------------------------------------------------------------------------------*/
    /*					Expense Defined Setters and Getters													  */
    /*--------------------------------------------------------------------------------------------------------*/

    /**
     * Set isDeleted
     *
     * @param boolean $isDeleted
     *
     * @return ExpenseEntity
     */
    public function setIsDeleted($isDeleted)
    {
        $this->isDeleted = $isDeleted;

        return $this;
    }

    /*--------------------------------------------------------------------------------------------------------*/
    /*					    Defined Setters and Getters													      */
    /*--------------------------------------------------------------------------------------------------------*/

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getAmount(): ?string
    {
        return $this->amount;
    }

    public function setAmount(string $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    public function getExpenseDate(): ?\DateTimeInterface
    {
        return $this->expenseDate;
    }

    public function setExpenseDate(?\DateTimeInterface $expenseDate): self
    {
        $this->expenseDate = $expenseDate;

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

    public function getExpenseType(): ?ExpenseTypeEntity
    {
        return $this->expenseType;
    }

    public function setExpenseType(?ExpenseTypeEntity $expenseType): self
    {
        $this->expenseType = $expenseType;

        return $this;
    }

    

    
   
}
