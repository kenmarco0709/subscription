<?php

namespace App\Entity;

use App\Entity\Base\BaseEntity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToStringTransformer;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ClientAccountRepository")
 * @ORM\Table(name="client_account")
 * @ORM\HasLifecycleCallbacks()
 */

class ClientAccountEntity extends BaseEntity
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
     * @ORM\Column(name="description", type="string")
     */
    protected $description;

    /**
     * @ORM\Column(name="remarks", type="text", nullable=true)
     */
    protected $remarks;

    /**
     * @ORM\Column(name="connection_type", type="string")
     */
    protected $connectionType;


    /**
     * @ORM\Column(name="old_balance", type="decimal", precision=12, scale=2, nullable=true)
     */
    protected $oldBalance;


    /**
     * @ORM\Column(name="remaining_balance", type="decimal", precision=12, scale=2, nullable=true)
     */
    protected $remainingBalance;
    
    /**
     * @ORM\Column(name="final_balance", type="decimal", precision=12, scale=2, nullable=true)
     */
    protected $finalBalance;

    /**
     * @ORM\ManyToOne(targetEntity="ClientEntity", inversedBy="clientAccounts")
     * @ORM\JoinColumn(name="client_id", referencedColumnName="id", nullable=true)
     */
    protected $client;

    /**
     * @ORM\OneToMany(targetEntity="ClientAccountBillingEntity", mappedBy="clientAccount", cascade={"remove"})
     * @ORM\OrderBy({"billingDate" = "ASC"})
     */
    protected $clientAccountBillings;

    /**
     * @ORM\OneToMany(targetEntity="ClientAccountPaymentEntity", mappedBy="clientAccount", cascade={"remove"})
     */
    protected $clientAccountPayments;

    /**
     * @ORM\OneToMany(targetEntity="BranchSmsEntity", mappedBy="clientAccount", cascade={"remove"})
     */
    protected $branchSmss;



    public function __construct($data = null)
    {
        $this->clientAccountBillings = new ArrayCollection();
        $this->clientAccountPayments = new ArrayCollection();
        $this->branchSmss = new ArrayCollection();
    }

    /*--------------------------------------------------------------------------------------------------------*/
    /*					User Defined Setters and Getters													  */
    /*--------------------------------------------------------------------------------------------------------*/


    /**
     * Get lastBillingDateAsString
     *
     *
     * @return string
     */
    public function  getLastBillingDateAsString()
    {

        foreach($this->getClientAccountBillings() as $k => $billing){
            if(!$billing->getIsDeleted()){
                return  date_format($billing->getBillingDate(), "m/d/Y");
            }
        
        }
        return '';
    }

    /**
     * Set isDeleted
     *
     * @param boolean $isDeleted
     *
     * @return ClientAccountEntity
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

    public function getPlan(): ?string
    {
        return $this->plan;
    }

    public function setPlan(string $plan): self
    {
        $this->plan = $plan;

        return $this;
    }

    public function getOldBalance(): ?string
    {
        return $this->oldBalance;
    }

    public function setOldBalance(?string $oldBalance): self
    {
        $this->oldBalance = $oldBalance;

        return $this;
    }

    public function getRemainingBalance(): ?string
    {
        return $this->remainingBalance;
    }

    public function setRemainingBalance(?string $remainingBalance): self
    {
        $this->remainingBalance = $remainingBalance;

        return $this;
    }

    public function getFinalBalance(): ?string
    {
        return $this->finalBalance;
    }

    public function setFinalBalance(?string $finalBalance): self
    {
        $this->finalBalance = $finalBalance;

        return $this;
    }

    

    public function getClient(): ?ClientEntity
    {
        return $this->client;
    }

    public function setClient(?ClientEntity $client): self
    {
        $this->client = $client;

        return $this;
    }

    /**
     * @return Collection<int, ClientAccountBillingEntity>
     */
    public function getClientAccountBillings(): Collection
    {
        return $this->clientAccountBillings;
    }

    public function addClientAccountBilling(ClientAccountBillingEntity $clientAccountBilling): self
    {
        if (!$this->clientAccountBillings->contains($clientAccountBilling)) {
            $this->clientAccountBillings[] = $clientAccountBilling;
            $clientAccountBilling->setClientAccount($this);
        }

        return $this;
    }

    public function removeClientAccountBilling(ClientAccountBillingEntity $clientAccountBilling): self
    {
        if ($this->clientAccountBillings->removeElement($clientAccountBilling)) {
            // set the owning side to null (unless already changed)
            if ($clientAccountBilling->getClientAccount() === $this) {
                $clientAccountBilling->setClientAccount(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ClientAccountPaymentEntity>
     */
    public function getClientAccountPayments(): Collection
    {
        return $this->clientAccountPayments;
    }

    public function addClientAccountPayment(ClientAccountPaymentEntity $clientAccountPayment): self
    {
        if (!$this->clientAccountPayments->contains($clientAccountPayment)) {
            $this->clientAccountPayments[] = $clientAccountPayment;
            $clientAccountPayment->setClientAccount($this);
        }

        return $this;
    }

    public function removeClientAccountPayment(ClientAccountPaymentEntity $clientAccountPayment): self
    {
        if ($this->clientAccountPayments->removeElement($clientAccountPayment)) {
            // set the owning side to null (unless already changed)
            if ($clientAccountPayment->getClientAccount() === $this) {
                $clientAccountPayment->setClientAccount(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, BranchSmsEntity>
     */
    public function getBranchSmss(): Collection
    {
        return $this->branchSmss;
    }

    public function addBranchSmss(BranchSmsEntity $branchSmss): self
    {
        if (!$this->branchSmss->contains($branchSmss)) {
            $this->branchSmss[] = $branchSmss;
            $branchSmss->setClientAccount($this);
        }

        return $this;
    }

    public function removeBranchSmss(BranchSmsEntity $branchSmss): self
    {
        if ($this->branchSmss->removeElement($branchSmss)) {
            // set the owning side to null (unless already changed)
            if ($branchSmss->getClientAccount() === $this) {
                $branchSmss->setClientAccount(null);
            }
        }

        return $this;
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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getConnectionType(): ?string
    {
        return $this->connectionType;
    }

    public function setConnectionType(string $connectionType): self
    {
        $this->connectionType = $connectionType;

        return $this;
    }

    public function getRemarks(): ?string
    {
        return $this->remarks;
    }

    public function setRemarks(?string $remarks): self
    {
        $this->remarks = $remarks;

        return $this;
    }

    

   
    
}
