<?php

namespace App\Entity;

use App\Entity\Base\BaseEntity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToStringTransformer;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ClientAccountPaymentRepository")
 * @ORM\Table(name="client_account_payment")
 * @ORM\HasLifecycleCallbacks()
 */

class ClientAccountPaymentEntity extends BaseEntity
{
    /**
     * @ORM\Column(type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    
    /**
     * @ORM\Column(name="transaction_no", type="string")
     */
    protected $transactionNo;

    /**
     * @ORM\Column(name="amount", type="decimal", precision=12, scale=2)
     */
    protected $amount;

    /**
     * @ORM\Column(name="amount_tendered", type="decimal", precision=12, scale=2, nullable=true)
     */
    protected $amountTendered;

    /**
     * @ORM\Column(name="amount_change", type="decimal", precision=12, scale=2, nullable=true)
     */
    protected $amountChange;

    /**
     * @ORM\Column(name="ref_no", type="string", nullable=true)
     */
    protected $refNo;

    /**
     * @ORM\Column(name="payment_date", type="datetime", nullable=true)
     */
    protected $paymentDate;

    /**
     * @ORM\Column(name="file_description", type="string", nullable=true)
     */
    protected $fileDescription;

    /**
     * @ORM\Column(name="parsed_file_description", type="string", nullable=true)
     */
    protected $parsedFileDescription;

    /**
     * @ORM\ManyToOne(targetEntity="ClientAccountEntity", inversedBy="clientAccountPayments")
     * @ORM\JoinColumn(name="client_account_id", referencedColumnName="id", nullable=true)
     */
    protected $clientAccount;
    
    /**
     * @ORM\ManyToOne(targetEntity="PaymentTypeEntity", inversedBy="clientAccountPayments")
     * @ORM\JoinColumn(name="payment_type_id", referencedColumnName="id", nullable=true)
     */
    protected $paymentType;



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
     * @return ClientAccountPaymentEntity
     */
    public function setIsDeleted($isDeleted)
    {
        $this->isDeleted = $isDeleted;

        return $this;
    }

    
    /**
     * Remove the file from the disk
     *
     * @ORM\PreRemove
     */
    public function removeFile() {

        $file = $this->getUploadRootDir() . '/' . $this->parsedFileDescription;
        if(!empty($this->profilePic) && file_exists($file)) unlink($file);
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

        $parsedDesc = $this->parsedFileDescription;
        $file = $this->getUploadRootDir() . '/' . $parsedDesc;
     
        if(!empty($parsedDesc) ) {
            return   $this->getUploadDir() . '/' . $parsedDesc;
        } else {
            return '';
        }

       
    }


    /*--------------------------------------------------------------------------------------------------------*/
    /*					    Defined Setters and Getters													      */
    /*--------------------------------------------------------------------------------------------------------*/

    public function getId(): ?string
    {
        return $this->id;
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

    public function getRefNo(): ?string
    {
        return $this->refNo;
    }

    public function setRefNo(?string $refNo): self
    {
        $this->refNo = $refNo;

        return $this;
    }

    public function getPaymentDate(): ?\DateTimeInterface
    {
        return $this->paymentDate;
    }

    public function setPaymentDate(?\DateTimeInterface $paymentDate): self
    {
        $this->paymentDate = $paymentDate;

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

    public function getPaymentType(): ?PaymentTypeEntity
    {
        return $this->paymentType;
    }

    public function setPaymentType(?PaymentTypeEntity $paymentType): self
    {
        $this->paymentType = $paymentType;

        return $this;
    }

    public function getTransactionNo(): ?string
    {
        return $this->transactionNo;
    }

    public function setTransactionNo(string $transactionNo): self
    {
        $this->transactionNo = $transactionNo;

        return $this;
    }

    public function getAmountTendered(): ?string
    {
        return $this->amountTendered;
    }

    public function setAmountTendered(?string $amountTendered): self
    {
        $this->amountTendered = $amountTendered;

        return $this;
    }

    public function getAmountChange(): ?string
    {
        return $this->amountChange;
    }

    public function setAmountChange(?string $amountChange): self
    {
        $this->amountChange = $amountChange;

        return $this;
    }

    public function getFileDescription(): ?string
    {
        return $this->fileDescription;
    }

    public function setFileDescription(?string $fileDescription): self
    {
        $this->fileDescription = $fileDescription;

        return $this;
    }

    public function getParsedFileDescription(): ?string
    {
        return $this->parsedFileDescription;
    }

    public function setParsedFileDescription(?string $parsedFileDescription): self
    {
        $this->parsedFileDescription = $parsedFileDescription;

        return $this;
    }

    

    

 
    
}
