<?php

namespace RestBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UserVisit
 *
 * @ORM\Table(name="user_visit")
 * @ORM\Entity(repositoryClass="RestBundle\Repository\UserVisitRepository")
 */
class UserVisit
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     *
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $user;

    /**
     *
     * @ORM\Column(name="visitDate", type="date")
     */
    private $visitDate;


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param mixed $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }


    /**
     * Set visitDate
     *
     * @param \DateTime $visitDate
     *
     * @return UserVisit
     */
    public function setVisitDate()
    {
        $this->visitDate = new \DateTime;

        return $this;
    }

    /**
     * Get visitDate
     *
     * @return \DateTime
     */
    public function getVisitDate()
    {
        return $this->visitDate;
    }
}

