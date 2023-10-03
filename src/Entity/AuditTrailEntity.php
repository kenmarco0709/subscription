<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\AuditTrailRepository")
 * @ORM\Table(name="audit_trail")
 * @ORM\HasLifecycleCallbacks()
 */

class AuditTrailEntity
{
    // Entity holder
    public $entity;

    // Original details holder
    private $originalDetails;

    /**
     * @ORM\Column(type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(name="ref_table", type="string")
     */
    protected $refTable;

    /**
     * @ORM\Column(name="ref_table_id", type="bigint")
     */
    protected $refTableId;

    /**
     * @ORM\Column(name="ref_table_label", type="string")
     */
    protected $refTableLabel;

    /**
     * @ORM\Column(name="action", type="string")
     */
    protected $action;

    /**
     * @ORM\Column(name="details", type="text")
     */
    protected $details;

    /**
     * @ORM\Column(name="created_by", type="string", length=50)
     */
    protected $createdBy;

    /**
     * @ORM\Column(name="created_at", type="datetime")
     */
    protected $createdAt;

    /**
     * @ORM\ManyToOne(targetEntity="UserEntity", inversedBy="auditTrails")
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
        $this->createdBy = isset($_COOKIE['username']) ? $_COOKIE['username'] : 'System';
        $this->createdAt = new \DateTime();
    }

    public function __construct($data=null)
    {
        if(!is_null($data)) {
        }
    }

    /*--------------------------------------------------------------------------------------------------------*/
    /*					User Defined Functions          													  */
    /*--------------------------------------------------------------------------------------------------------*/

    public function parseInformation($action, $params=null) {

        $this->refTableId = $this->entity->getId();
        $this->action = $action;

        $func = $this->refTable . '__' . str_replace(' ', '', $action);

        if(!is_null($params)) {
            $this->$func($params);
        } else {
            $this->$func();
        }

        return $this;
    }

    public function parseOriginalDetails() {

        $func = $this->refTable . '__parseDetails';
        $this->originalDetails = $this->$func();

        return $this;
    }

    private function user__New() {

        $this->details = json_encode(array(
            array('label' => 'Details',
                'columns' => $this->user__parseDetails()
            )
        ));

        return $this;
    }

    private function user__Update() {

        $this->details = json_encode(array(
            array('label' => 'Original Details',
                'columns' => $this->originalDetails
            ),
            array('label' => 'New Details',
                'columns' => $this->user__parseDetails()
            )
        ));

        return $this;
    }

    private function user__Delete() {

        $this->user__New();

        return $this;
    }

    private function user__parseDetails() {

        $this->refTableLabel = 'User';
        $company = $this->entity->getCompany();
        $branch = $this->entity->getBranch();


        return array(
            array(
                'display' => true,
                'name' => 'username',
                'label' => 'Username',
                'type' => 'local',
                'val' => $this->entity->getUsername()
            ),
            array(
                'display' => true,
                'name' => 'type',
                'label' => 'Type',
                'type' => 'local',
                'val' => $this->entity->getType()
            ),
            array(
                'display' => true,
                'name' => 'first_name',
                'label' => 'First Name',
                'type' => 'local',
                'val' => $this->entity->getFirstName()
            ),
            array(
                'display' => true,
                'name' => 'middle_name',
                'label' => 'Middle Name',
                'type' => 'local',
                'val' => $this->entity->getMiddleName()
            ),
            array(
                'display' => true,
                'name' => 'last_name',
                'label' => 'Last Name',
                'type' => 'local',
                'val' => $this->entity->getLastName()
            ),
            array(
                'display' => true,
                'name' => 'email',
                'label' => 'Email',
                'type' => 'local',
                'val' => $this->entity->getEmail()
            ),
            array(
                'display' => true,
                'name' => 'gender',
                'label' => 'Gender',
                'type' => 'local',
                'val' => $this->entity->getGender() 
            ),
            array(
                'display' => true,
                'name' => 'address',
                'label' => 'Address',
                'type' => 'local',
                'val' => $this->entity->getAddress() 
            ),
            array(
                'display' => true,
                'name' => 'company_id',
                'label' => 'Company',
                'type' => 'foreign',
                'val' => $company ? $company->getId() : '',
                'text' => $company ? $company->getDescription() : ''
            ),
            array(
                'display' => true,
                'name' => 'branch_id',
                'label' => 'Branch',
                'type' => 'foreign',
                'val' => $branch ? $branch->getId() : '',
                'text' => $branch ? $branch->getDescription() : ''
            )
        );
    }

    private function client__New() {

        $this->details = json_encode(array(
            array('label' => 'Details',
                'columns' => $this->client__parseDetails()
            )
        ));

        return $this;
    }

    private function client__Update() {

        $this->details = json_encode(array(
            array('label' => 'Original Details',
                'columns' => $this->originalDetails
            ),
            array('label' => 'New Details',
                'columns' => $this->client__parseDetails()
            )
        ));

        return $this;
    }

    private function client__Delete() {

        $this->client__New();

        return $this;
    }

    private function client__parseDetails() {

        $this->refTableLabel = 'Client';
        $branch = $this->entity->getBranch();
        return array(
            array(
                'display' => true,
                'name' => 'first_name',
                'label' => 'First Name',
                'type' => 'local',
                'val' => $this->entity->getFirstName()
            ),
            array(
                'display' => true,
                'name' => 'middle_name',
                'label' => 'Middle Name',
                'type' => 'local',
                'val' => $this->entity->getMiddleName()
            ),
            array(
                'display' => true,
                'name' => 'last_name',
                'label' => 'Last Name',
                'type' => 'local',
                'val' => $this->entity->getLastName()
            ),
            array(
                'display' => true,
                'name' => 'email',
                'label' => 'Email',
                'type' => 'local',
                'val' => $this->entity->getEmail()
            ),
            array(
                'display' => true,
                'name' => 'address',
                'label' => 'Address',
                'type' => 'local',
                'val' => $this->entity->getAddress() 
            ),
            array(
                'display' => true,
                'name' => 'contact_no',
                'label' => 'Contact No.',
                'type' => 'local',
                'val' => $this->entity->getContactNo() 
            ),
            array(
                'display' => true,
                'name' => 'branch_id',
                'label' => 'Branch',
                'type' => 'foreign',
                'val' => $branch ? $branch->getId() : '',
                'text' => $branch ? $branch->getDescription() : ''
            )
        );
    }

    

    /*--------------------------------------------------------------------------------------------------------*/
    /*					Setters and Getters																	  */
    /*--------------------------------------------------------------------------------------------------------*/

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getRefTable(): ?string
    {
        return $this->refTable;
    }

    public function setRefTable(string $refTable): self
    {
        $this->refTable = $refTable;

        return $this;
    }

    public function getRefTableId(): ?string
    {
        return $this->refTableId;
    }

    public function setRefTableId(string $refTableId): self
    {
        $this->refTableId = $refTableId;

        return $this;
    }

    public function getRefTableLabel(): ?string
    {
        return $this->refTableLabel;
    }

    public function setRefTableLabel(string $refTableLabel): self
    {
        $this->refTableLabel = $refTableLabel;

        return $this;
    }

    public function getAction(): ?string
    {
        return $this->action;
    }

    public function setAction(string $action): self
    {
        $this->action = $action;

        return $this;
    }

    public function getDetails(): ?string
    {
        return $this->details;
    }

    public function setDetails(string $details): self
    {
        $this->details = $details;

        return $this;
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
