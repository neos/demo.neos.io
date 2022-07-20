<?php
namespace Neos\Demo\UserRegistration\Aspects;

use Neos\Demo\UserRegistration\Domain\Service\User\UserRoleService;
use Neos\Demo\UserRegistration\Resource\ResourceCollectionName;
use Neos\Flow\Annotations as Flow;
use Neos\Http\Factories\FlowUploadedFile;

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
     * @Flow\Before("method(Neos\Flow\ResourceManagement\ResourceManager->[importResource|importResourceFromContent|importUploadedResource]())")
     * @return void
     */
    public function modifyCollectionNameBasedOnRole(\Neos\Flow\AOP\JoinPointInterface $joinPoint) {

        if ($this->userRoleService->isRestrictedEditor()) {
            $collectionName = $joinPoint->getMethodArgument('collectionName');
            $collectionName = ResourceCollectionName::PROTECTED_RESOURCES_COLLECTION_NAME;
            $joinPoint->setMethodArgument('collectionName', $collectionName);
        }
    }
}
