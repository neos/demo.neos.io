<?php
namespace Neos\Demo\UserRegistration\Domain\Service\Asset;

use Neos\ContentRepository\Domain\Model\NodeInterface;
use Neos\Demo\UserRegistration\Domain\Service\User\UserRoleService;
use Neos\Flow\Annotations as Flow;
use Neos\Media\Domain\Model\Asset;
use Neos\Media\Domain\Model\AssetCollection;
use Neos\Media\Domain\Model\AssetInterface;
use Neos\Media\Domain\Model\Thumbnail;
use Neos\Media\Domain\Repository\AssetCollectionRepository;
use Neos\Neos\Service\UserService;
use Neos\Flow\Security\Policy\PolicyService;
use Psr\Log\LoggerInterface;

/**
 * @Flow\Scope("singleton")
 */
class AssetManipulator {

    /**
     * @Flow\Inject
     * @var AssetCollectionRepository
     */
    protected $assetCollectionRepository;

    /**
     * @Flow\Inject
     * @var UserService
     */
    protected $userService;

    /**
     * @Flow\Inject
     * @var UserRoleService
     */
    protected $userRoleService;

    /**
     * @Flow\Inject
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param Asset $asset
     * @param NodeInterface $node
     * @param string $propertyName
     * @throws \Neos\Flow\Persistence\Exception\IllegalObjectTypeException
     */
    public function assetUploaded(Asset $asset, NodeInterface $node, string $propertyName) {
        $this->addAssetCollectionToAsset($asset);
    }

    /**
     * @param AssetInterface $asset
     */
    public function assetCreated(AssetInterface $asset) {
        $this->addAssetCollectionToAsset($asset);
    }

    /**
     * Generic method for adding the collection to the asset.
     *
     * @param mixed $asset
     */
    protected function addAssetCollectionToAsset($asset)
    {
        if ($this->userRoleService->isRestrictedEditor() === false) {
            return;
        }
        $userAssetCollectionName = $this->userService->getPersonalWorkspaceName();
        $userAssetCollection = $this->assetCollectionRepository->findOneByTitle($userAssetCollectionName);
        if ($userAssetCollection === null) {
            $this->logger->debug("No asset collection found with the title " . $userAssetCollectionName);
            $userAssetCollection = new AssetCollection($userAssetCollectionName);
        }
        /* @var $existingAssetCollections \Doctrine\Common\Collections\Collection */
        $assetCollections = $asset->getAssetCollections();
        if($assetCollections->contains($userAssetCollectionName) === true) {
            return;
        }

        $userAssetCollection->addAsset($asset);
        $this->assetCollectionRepository->update($userAssetCollection);
    }

}
