<?php

namespace App\Entity;

use App\Entity\Base\BaseEntity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToStringTransformer;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CompanyRepository")
 * @ORM\Table(name="company")
 * @ORM\HasLifecycleCallbacks()
 */

class CompanyEntity extends BaseEntity
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
     * @ORM\Column(name="address", type="string", nullable=true)
     */
    protected $address;

    /**
     * @ORM\Column(name="contact_no", type="string", nullable=true)
     */
    protected $contactNo;

    /**
     * @ORM\Column(name="email", type="string", nullable=true)
     */
    protected $email;

    /**
     * @ORM\Column(name="logo_desc", type="string", nullable=true)
     */
    protected $logoDesc;
    
     /**
     * @ORM\Column(name="parsed_logo_desc", type="string", nullable=true)
     */
    protected $parsedLogoDesc;

    /**
     * @ORM\OneToMany(targetEntity="UserEntity", mappedBy="company", cascade={"remove"})
     */
    protected $users;

    /**
     * @ORM\OneToMany(targetEntity="BranchEntity", mappedBy="company", cascade={"remove"})
     */
    protected $branches;

      /**
     * @ORM\OneToMany(targetEntity="CompanyAccessEntity", mappedBy="company", cascade={"remove"})
     */
    protected $companyAccesses;

    
      /**
     * @ORM\OneToMany(targetEntity="SmsEntity", mappedBy="company", cascade={"remove"})
     */
    protected $smss;





    public function __construct($data = null)
    {
        $this->users = new ArrayCollection();
        $this->branches = new ArrayCollection();
        $this->companyAccesses = new ArrayCollection();
        $this->smss = new ArrayCollection();
    }

    /*--------------------------------------------------------------------------------------------------------*/
    /*					Company Defined Setters and Getters													  */
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
     * @return CompanyEntity
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

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getLogoDesc(): ?string
    {
        return $this->logoDesc;
    }

    public function setLogoDesc(?string $logoDesc): self
    {
        $this->logoDesc = $logoDesc;

        return $this;
    }

    public function getParsedLogoDesc(): ?string
    {
        return $this->parsedLogoDesc;
    }

    public function setParsedLogoDesc(?string $parsedLogoDesc): self
    {
        $this->parsedLogoDesc = $parsedLogoDesc;

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
            $user->setCompany($this);
        }

        return $this;
    }

    public function removeUser(UserEntity $user): self
    {
        if ($this->users->removeElement($user)) {
            // set the owning side to null (unless already changed)
            if ($user->getCompany() === $this) {
                $user->setCompany(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, BranchEntity>
     */
    public function getBranches(): Collection
    {
        return $this->branches;
    }

    public function addBranch(BranchEntity $branch): self
    {
        if (!$this->branches->contains($branch)) {
            $this->branches[] = $branch;
            $branch->setCompany($this);
        }

        return $this;
    }

    public function removeBranch(BranchEntity $branch): self
    {
        if ($this->branches->removeElement($branch)) {
            // set the owning side to null (unless already changed)
            if ($branch->getCompany() === $this) {
                $branch->setCompany(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, CompanyAccessEntity>
     */
    public function getCompanyAccesses(): Collection
    {
        return $this->companyAccesses;
    }

    public function addCompanyAccess(CompanyAccessEntity $companyAccess): self
    {
        if (!$this->companyAccesses->contains($companyAccess)) {
            $this->companyAccesses[] = $companyAccess;
            $companyAccess->setCompany($this);
        }

        return $this;
    }

    public function removeCompanyAccess(CompanyAccessEntity $companyAccess): self
    {
        if ($this->companyAccesses->removeElement($companyAccess)) {
            // set the owning side to null (unless already changed)
            if ($companyAccess->getCompany() === $this) {
                $companyAccess->setCompany(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, SmsEntity>
     */
    public function getSmss(): Collection
    {
        return $this->smss;
    }

    public function addSmss(SmsEntity $smss): self
    {
        if (!$this->smss->contains($smss)) {
            $this->smss[] = $smss;
            $smss->setCompany($this);
        }

        return $this;
    }

    public function removeSmss(SmsEntity $smss): self
    {
        if ($this->smss->removeElement($smss)) {
            // set the owning side to null (unless already changed)
            if ($smss->getCompany() === $this) {
                $smss->setCompany(null);
            }
        }

        return $this;
    }

    

    

   
}
