<?php
namespace Neos\Demo\UserRegistration\Aspects;

use Neos\Demo\UserRegistration\Domain\Service\User\UserRoleService;
use Neos\Demo\UserRegistration\Resource\ResourceCollectionName;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\AOP\JoinPointInterface;

/**
 *
 * @Flow\Aspect
 */
class ModifyResourceCollectionNameAspect {

    /**
     * @Flow\Inject
     * @var UserRoleService
     */
    protected $userRoleService;

    /**
     * @Flow\Before("method(Neos\Flow\ResourceManagement\ResourceManager->import.*())")
     */
    public function modifyCollectionNameBasedOnRole(JoinPointInterface $joinPoint): void {
        if ($this->userRoleService->isRestrictedEditor()) {
            $collectionName = ResourceCollectionName::PROTECTED_RESOURCES_COLLECTION_NAME;
            $joinPoint->setMethodArgument('collectionName', $collectionName);
        }
    }
}
