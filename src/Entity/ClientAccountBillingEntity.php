<?php

namespace App\Entity;

use App\Entity\Base\BaseEntity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToStringTransformer;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ClientAccountBillingRepository")
 * @ORM\Table(name="client_account_billing")
 * @ORM\HasLifecycleCallbacks()
 */

class ClientAccountBillingEntity extends BaseEntity
{
    /**
     * @ORM\Column(type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(name="status", type="string")
     */
    protected $status;
    
    /**
     * @ORM\Column(name="billed_amount", type="decimal", precision=12, scale=2, nullable=true)
     */
    protected $billedAmount;

    /**
     * @ORM\Column(name="billing_date", type="datetime", nullable=true)
     */
    protected $billingDate;

    /**
     * @ORM\Column(name="due_date", type="datetime", nullable=true)
     */
    protected $dueDate;

    /**
     * @ORM\ManyToOne(targetEntity="ClientAccountEntity", inversedBy="clientAccountBillings")
     * @ORM\JoinColumn(name="client_account_id", referencedColumnName="id", nullable=true)
     */
    protected $clientAccount;



    public function __construct($data = null)
    {
    }

    /*--------------------------------------------------------------------------------------------------------*/
    /*					User Defined Setters and Getters													  */
    /*--------------------------------------------------------------------------------------------------------*/

    /**
     * Set isDeleted
     *
     * @param boolean $isDeleted
     *
     * @return ClientAccountBillingEntity
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

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getBilledAmount(): ?string
    {
        return $this->billedAmount;
    }

    public function setBilledAmount(?string $billedAmount): self
    {
        $this->billedAmount = $billedAmount;

        return $this;
    }

    public function getBillingDate(): ?\DateTimeInterface
    {
        return $this->billingDate;
    }

    public function setBillingDate(?\DateTimeInterface $billingDate): self
    {
        $this->billingDate = $billingDate;

        return $this;
    }

    public function getDueDate(): ?\DateTimeInterface
    {
        return $this->dueDate;
    }

    public function setDueDate(?\DateTimeInterface $dueDate): self
    {
        $this->dueDate = $dueDate;

        return $this;
    }

    public function getClientAccount(): ?ClientAccountEntity
    {
        return $this->clientAccount;
    }

    public function setClientAccount(?ClientAccountEntity $clientAccount): self
    {
        $this->clientAccount = $clientAccount;

        return $this;
    }

    

    
}
