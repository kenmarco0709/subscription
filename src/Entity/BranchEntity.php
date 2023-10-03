<?php

namespace App\Entity;

use App\Entity\Base\BaseEntity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToStringTransformer;

/**
 * @ORM\Entity(repositoryClass="App\Repository\BranchRepository")
 * @ORM\Table(name="branch")
 * @ORM\HasLifecycleCallbacks()
 */

class BranchEntity extends BaseEntity
{
    /**
     * @ORM\Column(type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(name="code", type="string")
     */
    protected $code;
    
    /**
     * @ORM\Column(name="description", type="string")
     */
    protected $description;

    /**
     * @ORM\ManyToOne(targetEntity="CompanyEntity", inversedBy="branches")
     * @ORM\JoinColumn(name="company_id", referencedColumnName="id", nullable=true)
     */
    protected $company;

    /**
     * @ORM\OneToMany(targetEntity="UserEntity", mappedBy="branch", cascade={"remove"})
     */
    protected $users;

    /**
     * @ORM\OneToMany(targetEntity="PurokEntity", mappedBy="branch", cascade={"remove"})
     */
    protected $puroks;

    /**
     * @ORM\OneToMany(targetEntity="BranchVariableEntity", mappedBy="branch", cascade={"remove"})
     */
    protected $branchVariables;

     /**
     * @ORM\OneToMany(targetEntity="ClientEntity", mappedBy="branch", cascade={"remove"})
     */
    protected $clients;

     /**
     * @ORM\OneToMany(targetEntity="PaymentTypeEntity", mappedBy="branch", cascade={"remove"})
     */
    protected $paymentTypes;

     /**
     * @ORM\OneToMany(targetEntity="BranchSmsEntity", mappedBy="branch", cascade={"remove"})
     */
    protected $branchSmss;


    public function __construct($data = null)
    {
        $this->users = new ArrayCollection();
        $this->clients = new ArrayCollection();
        $this->puroks = new ArrayCollection();
        $this->branchVariables = new ArrayCollection();
        $this->paymentTypes = new ArrayCollection();
        $this->branchSmss = new ArrayCollection();
    }

    /*--------------------------------------------------------------------------------------------------------*/
    /*					Branch Defined Setters and Getters													  */
    /*--------------------------------------------------------------------------------------------------------*/

/**
     * Set isDeleted
     *
     * @param boolean $isDeleted
     *
     * @return BranchEntity
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

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;

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

    public function getCompany(): ?CompanyEntity
    {
        return $this->company;
    }

    public function setCompany(?CompanyEntity $company): self
    {
        $this->company = $company;

        return $this;
    }

    /**
     * @return Collection<int, UserEntity>
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(UserEntity $user): self
    {
        if (!$this->users->contains($user)) {
            $this->users[] = $user;
            $user->setBranch($this);
        }

        return $this;
    }

    public function removeUser(UserEntity $user): self
    {
        if ($this->users->removeElement($user)) {
            // set the owning side to null (unless already changed)
            if ($user->getBranch() === $this) {
                $user->setBranch(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ClientEntity>
     */
    public function getClients(): Collection
    {
        return $this->clients;
    }

    public function addClient(ClientEntity $client): self
    {
        if (!$this->clients->contains($client)) {
            $this->clients[] = $client;
            $client->setBranch($this);
        }

        return $this;
    }

    public function removeClient(ClientEntity $client): self
    {
        if ($this->clients->removeElement($client)) {
            // set the owning side to null (unless already changed)
            if ($client->getBranch() === $this) {
                $client->setBranch(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, PurokEntity>
     */
    public function getPuroks(): Collection
    {
        return $this->puroks;
    }

    public function addPurok(PurokEntity $purok): self
    {
        if (!$this->puroks->contains($purok)) {
            $this->puroks[] = $purok;
            $purok->setBranch($this);
        }

        return $this;
    }

    public function removePurok(PurokEntity $purok): self
    {
        if ($this->puroks->removeElement($purok)) {
            // set the owning side to null (unless already changed)
            if ($purok->getBranch() === $this) {
                $purok->setBranch(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, BranchVariableEntity>
     */
    public function getBranchVariables(): Collection
    {
        return $this->branchVariables;
    }

    public function addBranchVariable(BranchVariableEntity $branchVariable): self
    {
        if (!$this->branchVariables->contains($branchVariable)) {
            $this->branchVariables[] = $branchVariable;
            $branchVariable->setBranch($this);
        }

        return $this;
    }

    public function removeBranchVariable(BranchVariableEntity $branchVariable): self
    {
        if ($this->branchVariables->removeElement($branchVariable)) {
            // set the owning side to null (unless already changed)
            if ($branchVariable->getBranch() === $this) {
                $branchVariable->setBranch(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, PaymentTypeEntity>
     */
    public function getPaymentTypes(): Collection
    {
        return $this->paymentTypes;
    }

    public function addPaymentType(PaymentTypeEntity $paymentType): self
    {
        if (!$this->paymentTypes->contains($paymentType)) {
            $this->paymentTypes[] = $paymentType;
            $paymentType->setBranch($this);
        }

        return $this;
    }

    public function removePaymentType(PaymentTypeEntity $paymentType): self
    {
        if ($this->paymentTypes->removeElement($paymentType)) {
            // set the owning side to null (unless already changed)
            if ($paymentType->getBranch() === $this) {
                $paymentType->setBranch(null);
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
            $branchSmss->setBranch($this);
        }

        return $this;
    }

    public function removeBranchSmss(BranchSmsEntity $branchSmss): self
    {
        if ($this->branchSmss->removeElement($branchSmss)) {
            // set the owning side to null (unless already changed)
            if ($branchSmss->getBranch() === $this) {
                $branchSmss->setBranch(null);
            }
        }

        return $this;
    }

   

}
