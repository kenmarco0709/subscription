<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserAccessRepository")
 * @ORM\Table(name="user_access")
 * @ORM\HasLifecycleCallbacks()
 */

class UserAccessEntity
{
    /**
     * @ORM\Column(type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string")
     */
    protected $type;

    /**
     * @ORM\Column(name="description", type="string")
     */
    protected $description;

    /**
     * @ORM\Column(name="created_by", type="string", length=50)
     */
    protected $createdBy;

    /**
     * @ORM\Column(name="created_at", type="datetime", nullable=false)
     */
    protected $createdAt;

    /**
     * @ORM\Column(name="updated_by", type="string", length=50)
     */
    protected $updatedBy;

    /**
     * @ORM\Column(name="updated_at", type="datetime", nullable=false)
     */
    protected $updatedAt;

    /**
     * @ORM\ManyToOne(targetEntity="UserEntity", inversedBy="userAccesses")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    protected $user;

    /**
     * Set createdAt
     *
     * @ORM\PrePersist
     */
    public function setCreatedAtValue()
    {
        $this->createdBy = isSet($_COOKIE['username']) ? $_COOKIE['username'] : 'System';
        $this->createdAt = new \DateTime();
        $this->updatedBy = isSet($_COOKIE['username']) ? $_COOKIE['username'] : 'System';
        $this->updatedAt = new \DateTime();
    }

    /**
     * Set updatedAt
     *
     * @ORM\PreUpdate
     */
    public function setUpdatedAtValue()
    {
        $this->updatedBy = isSet($_COOKIE['username']) ? $_COOKIE['username'] : 'System';
        $this->updatedAt = new \DateTime();
    }

    public function __construct($data=null)
    {
        if(!is_null($data)) {
            $this->id = $data->getId();
            $this->type = $data->getType();
            $this->description = $data->getDescription();
            $this->user = $data->getUser();
        }
    }

    /*--------------------------------------------------------------------------------------------------------*/
    /*					User Defined Setters and Getters													  */
    /*--------------------------------------------------------------------------------------------------------*/

    /*--------------------------------------------------------------------------------------------------------*/
    /*					Setters and Getters																	  */
    /*--------------------------------------------------------------------------------------------------------*/



    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set type
     *
     * @param string $type
     *
     * @return UserAccessEntity
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return UserAccessEntity
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    public function getCreatedBy(): ?string
    {
        return $this->createdBy;
    }

    public function setCreatedBy(string $createdBy): self
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedBy(): ?string
    {
        return $this->updatedBy;
    }

    public function setUpdatedBy(string $updatedBy): self
    {
        $this->updatedBy = $updatedBy;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getUser(): ?UserEntity
    {
        return $this->user;
    }

    public function setUser(?UserEntity $user): self
    {
        $this->user = $user;

        return $this;
    }

  
}
