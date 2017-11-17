<?php

namespace Kunstmaan\AdminBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Kunstmaan\ApiBundle\Annotations as Api;

/**
 * User entity
 *
 * @ORM\Entity(repositoryClass="Kunstmaan\AdminBundle\Repository\UserRepository")
 * @ORM\Table(name="kuma_users")
 * @Api\ApiMeta()
 */
class User extends BaseUser
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Api\ApiColumn(type="id")
     */
    protected $id;

    /**
     * The doctrine metadata is set dynamically in Kunstmaan\AdminBundle\EventListener\MappingListener
     * @Api\ApiColumn(type="string")
     */
    protected $groups;

    /**
     * @ORM\Column(type="string", name="admin_locale", length=5, nullable=true)
     * @Api\ApiColumn(type="string")
     */
    protected $adminLocale;

    /**
     * @ORM\Column(type="boolean", name="password_changed", nullable=true)
     * @Api\ApiColumn(type="boolean")
     */
    protected $passwordChanged;

    /**
     * @ORM\Column(name="google_id", type="string", length=255, nullable=true)
     * @Api\ApiColumn(type="string")
     */
    protected $googleId;


    /**
     * Get the classname of the formtype.
     *
     * @return string
     */
    public function getFormTypeClass()
    {
        return 'Kunstmaan\AdminBundle\Form\UserType';
    }
}
