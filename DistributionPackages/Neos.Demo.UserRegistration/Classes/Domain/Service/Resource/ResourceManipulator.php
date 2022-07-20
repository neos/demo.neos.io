<?php
namespace Neos\Demo\UserRegistration\Domain\Service\Resource;

use Neos\Demo\UserRegistration\Domain\Service\User\UserRoleService;
use Neos\Demo\UserRegistration\Resource\ResourceCollectionName;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\ResourceManagement\PersistentResource;
use Neos\Media\Domain\Model\Thumbnail;
use Neos\Media\Domain\Repository\AssetCollectionRepository;
use Neos\Neos\Service\UserService;
use Psr\Log\LoggerInterface;

/**
 * @Flow\Scope("singleton")
 */
class ResourceManipulator {

    /**
     * @Flow\Inject
     * @var AssetCollectionRepository
     */
    protected $assetCollectionRepository;

    /**
     * @Flow\Inject
     * @var UserRoleService
     */
    protected $userRoleService;

    /**
     * @Flow\Inject
     * @var UserService
     */
    protected $userService;

    /**
     * @Flow\Inject
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param Thumbnail $thumbnail
     */
    public function thumbnailCreated(Thumbnail $thumbnail) {
        $this->addResourceToPrivateResources($thumbnail->getOriginalAsset()->getResource());
    }

    /**
     * Generic method for adding the collection to the asset.
     *
     * @param PersistentResource $resource
     */
    protected function addResourceToPrivateResources($resource)
    {
        if ($this->userRoleService->isRestrictedEditor()) {
            $resource->setCollectionName(ResourceCollectionName::PROTECTED_RESOURCES_COLLECTION_NAME);
        }
    }
}
