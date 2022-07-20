<?php
namespace Neos\Demo\UserRegistration\Domain\Service\Asset;

use Neos\Demo\UserRegistration\Domain\Service\User\UserRoleService;
use Neos\Demo\UserRegistration\Resource\ResourceCollectionName;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\ResourceManagement\PersistentResource;
use Neos\Media\Domain\Model\Thumbnail;

/**
 * @Flow\Scope("singleton")
 */
class ThumbnailManipulator {

    /**
     * @Flow\Inject
     * @var UserRoleService
     */
    protected $userRoleService;

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
