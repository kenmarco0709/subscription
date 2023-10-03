<?php

namespace App\Entity;

use App\Entity\Base\BaseEntity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToStringTransformer;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SmsRepository")
 * @ORM\Table(name="sms")
 * @ORM\HasLifecycleCallbacks()
 */

class SmsEntity extends BaseEntity
{
    /**
     * @ORM\Column(type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(name="sms_type", type="string")
     */
    protected $smsType;
    
    /**
     * @ORM\Column(name="message", type="text")
     */
    protected $message;

    /**
     * @ORM\ManyToOne(targetEntity="CompanyEntity", inversedBy="smss")
     * @ORM\JoinColumn(name="company_id", referencedColumnName="id", nullable=true)
     */
    protected $company;
    
    /**
     * @ORM\OneToMany(targetEntity="BranchSmsEntity", mappedBy="sms", cascade={"remove"})
     */
    protected $branchSmss;




    public function __construct($data = null)
    {
        $this->branchSmss = new ArrayCollection();
    }

    /*--------------------------------------------------------------------------------------------------------*/
    /*					Sms Defined Setters and Getters													  */
    /*--------------------------------------------------------------------------------------------------------*/

            /**
     * Remove the file from the disk
     *
     * @ORM\PreRemove
     */
    public function removeFile() {

        $file = $this->getUploadRootDir() . '/' . $this->parsedLogoDesc;
        if(!empty($this->logoDesc) && file_exists($file)) unlink($file);
    }

        /**
     * Get uploadDir
     *
     * @return string
     */
    public function getUploadDir() {

        return '/uploads/file';
    }

    /**
     * Get uploadRootDir
     *
     * @return string
     */
    public function getUploadRootDir() {

        return __DIR__ . './../../public' . $this->getUploadDir();
    }


    /**
     * get fileWebPath
     *
     * @return string
     */
    public function getFileWebPath() {

        $parsedDesc = $this->parsedLogoDesc;
        $file = $this->getUploadRootDir() . '/' . $parsedDesc;
     
        if(!empty($parsedDesc) ) {
            return   $this->getUploadDir() . '/' . $parsedDesc;
        } else {
            return '';
        }

       
    }


    /**
     * Set isDeleted
     *
     * @param boolean $isDeleted
     *
     * @return SmsEntity
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

    public function getSmsType(): ?string
    {
        return $this->smsType;
    }

    public function setSmsType(string $smsType): self
    {
        $this->smsType = $smsType;

        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(string $message): self
    {
        $this->message = $message;

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
            $branchSmss->setSms($this);
        }

        return $this;
    }

    public function removeBranchSmss(BranchSmsEntity $branchSmss): self
    {
        if ($this->branchSmss->removeElement($branchSmss)) {
            // set the owning side to null (unless already changed)
            if ($branchSmss->getSms() === $this) {
                $branchSmss->setSms(null);
            }
        }

        return $this;
    }

    

    

    
}
