<?php

namespace App\Entity\Base;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\MappedSuperclass()
 * @ORM\HasLifecycleCallbacks()
 */
abstract class BaseEntity
{
    /**
     * @ORM\Column(type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

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
     * @ORM\Column(name="is_deleted", type="boolean", nullable=true)
     */
    protected $isDeleted;

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
        $this->isDeleted = 0;
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

    /*--------------------------------------------------------------------------------------------------------*/
    /*					User Defined Setters and Getters													  */
    /*--------------------------------------------------------------------------------------------------------*/

    /**
     * Get idEncoded
     *
     * @return string
     */
    public function getIdEncoded() {

        return base64_encode($this->id);
    }

    /*--------------------------------------------------------------------------------------------------------*/
    /*					Setters and Getters																	  */
    /*--------------------------------------------------------------------------------------------------------*/
   
    /**
     * Get isDeleted
     *
     * @return string
     */
    public function getisDeleted()
    {
        return $this->isDeleted;
    }

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
     * Get createdBy
     *
     * @return string
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    /**
     * Get updatedBy
     *
     * @return string
     */
    public function getUpdatedBy()
    {
        return $this->updatedBy;
    }

       /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

       /**
     * Get updatedAt
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }
}
