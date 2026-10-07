<?php
// src/EventListener/EntityListener.php
namespace App\EventListener;

use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Uid\Uuid;

class EntityListener
{

    private Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

	/**
	 * @throws \Exception
	 */
	public function prePersist(PrePersistEventArgs $args): void
    {
        $entity = $args->getObject();
        $user = $this->security->getUser();


        if ($user && method_exists($entity, 'setCreatedBy')) {
            $entity->setCreatedBy($user);
        }

        if (method_exists($entity, 'setCreatedAt')) {
            $entity->setCreatedAt(new \DateTime('now'));
        }
        if ($user && method_exists($entity, 'setUpdatedBy')) {
            $entity->setUpdatedBy($user);
        }

        if (method_exists($entity, 'setUpdatedAt')) {
            $entity->setUpdatedAt(new \DateTime('now'));
        }

        if (method_exists($entity, 'setUuid')) {
            $entity->setUuid(Uuid::v4());
        }

        if (method_exists($entity, 'setToken')) {
            $entity->setToken(bin2hex(random_bytes(16)));
        }
    }

    public function preUpdate(PreUpdateEventArgs $args): void
    {
        $entity = $args->getObject();
        $user = $this->security->getUser();

        if ($user && method_exists($entity, 'setUpdatedBy')) {
            $entity->setUpdatedBy($user);
        }

        if (method_exists($entity, 'setUpdatedAt')) {
            $entity->setUpdatedAt(new \DateTime('now'));
        }
    }
}
