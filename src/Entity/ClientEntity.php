<?php

namespace App\Entity;

use App\Entity\Base\BaseEntity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToStringTransformer;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ClientRepository")
 * @ORM\Table(name="client")
 * @ORM\HasLifecycleCallbacks()
 */

class ClientEntity extends BaseEntity
{
    /**
     * @ORM\Column(type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(name="first_name", type="string", nullable=true)
     */
    protected $firstName;

    /**
     * @ORM\Column(name="middle_name", type="string", nullable=true)
     */
    protected $middleName;

    /**
     * @ORM\Column(name="last_name", type="string", nullable=true)
     */
    protected $lastName;

    /**
     * @ORM\Column(name="email", type="string", nullable=true)
     */
    protected $email;

    /**
     * @ORM\Column(name="address", type="string", nullable=true)
     */
    protected $address;

    /**
     * @ORM\Column(name="contact_no", type="string", nullable=true)
     */
    protected $contactNo;

     /**
     * @ORM\ManyToOne(targetEntity="BranchEntity", inversedBy="clients")
     * @ORM\JoinColumn(name="branch_id", referencedColumnName="id", nullable=true)
     */
    protected $branch;
    
    /**
     * @ORM\OneToMany(targetEntity="ClientAccountEntity", mappedBy="client", cascade={"remove"})
     */
    protected $clientAccounts;



    public function __construct()
    {
        $this->clientAccounts = new ArrayCollection();
    }

    
    /*--------------------------------------------------------------------------------------------------------*/
    /*					Client Defined Setters and Getters													  */
    /*--------------------------------------------------------------------------------------------------------*/

    /**
     * Get fullName
     *
     * @return string
     */
    public function getFullName() {

        return $this->firstName . ' ' . $this->lastName;
    }


/**
     * Set isDeleted
     *
     * @param boolean $isDeleted
     *
     * @return ClientEntity
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

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(?string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getMiddleName(): ?string
    {
        return $this->middleName;
    }

    public function setMiddleName(?string $middleName): self
    {
        $this->middleName = $middleName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(?string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function getContactNo(): ?string
    {
        return $this->contactNo;
    }

    public function setContactNo(?string $contactNo): self
    {
        $this->contactNo = $contactNo;

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

    /**
     * @return Collection<int, ClientAccountEntity>
     */
    public function getClientAccounts(): Collection
    {
        return $this->clientAccounts;
    }

    public function addClientAccount(ClientAccountEntity $clientAccount): self
    {
        if (!$this->clientAccounts->contains($clientAccount)) {
            $this->clientAccounts[] = $clientAccount;
            $clientAccount->setClient($this);
        }

        return $this;
    }

    public function removeClientAccount(ClientAccountEntity $clientAccount): self
    {
        if ($this->clientAccounts->removeElement($clientAccount)) {
            // set the owning side to null (unless already changed)
            if ($clientAccount->getClient() === $this) {
                $clientAccount->setClient(null);
            }
        }

        return $this;
    }

    

    

   
}
