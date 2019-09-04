<?php

namespace App\Entity;

use Gedmo\Blameable\Traits\BlameableEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;
use Rollerworks\Component\PasswordStrength\Validator\Constraints as RollerworksPassword;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity
 * @ORM\Table(name="fos_user", indexes={
 *   @ORM\Index(name="search_User_enabled", columns={"enabled"}),
 *   @ORM\Index(name="search_User_username", columns={"username"}),
 *   @ORM\Index(name="search_User_name", columns={"name"}),
 *   @ORM\Index(name="search_User_email", columns={"email"})
 * })
 *
 * @Gedmo\Loggable
 */
class User extends BaseUser
{
    use BlameableEntity;
    use TimestampableEntity;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=true)
     *
     * @Gedmo\Versioned
     */
    protected $name;

    /**
     * Plain password. Used for model validation. Must not be persisted.
     *
     * @var string
     *
     * @RollerworksPassword\PasswordStrength(
     *     minLength=8,
     *     minStrength=3,
     *     tooShortMessage="Kodeord skal være på mindst {{length}} tegn"
     * )
     * @RollerworksPassword\PasswordRequirements(
     *     minLength=8,
     *     requireLetters=true,
     *     requireNumbers=true,
     *     requireCaseDiff=true,
     *     tooShortMessage="Kodeord skal være på mindst {{length}} tegn",
     *     requireCaseDiffMessage="Kodeordet skal indeholde både store og små bogstaver",
     *     missingNumbersMessage="Kodeordet skal indeholde tal",
     *     missingSpecialCharacterMessage="Kodeordet skal indeholde specialtegn"
     * )
     */
    protected $plainPassword;

    /**
     * @var bool
     *
     * @Gedmo\Versioned
     */
    protected $enabled;

    /**
     * @var string
     *
     * @Gedmo\Versioned
     */
    protected $email;

    /**
     * @var array
     *
     * @Gedmo\Versioned
     */
    protected $roles;

    /**
     * User constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->setEnabled(true);
    }

    /**
     * Set name.
     *
     * @param string $name
     *
     * @return User
     */
    public function setName($name): User
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * __toString.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->name ?? $this->getUsernameCanonical();
    }
}
