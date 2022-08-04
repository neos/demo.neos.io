<?php
namespace Neos\Demo\UserRegistration\Domain\Service\Asset;

use Neos\ContentRepository\Domain\Model\NodeInterface;
use Neos\Demo\UserRegistration\Domain\Service\User\UserRoleService;
use Neos\Flow\Annotations as Flow;
use Neos\Media\Domain\Model\Asset;
use Neos\Media\Domain\Model\AssetCollection;
use Neos\Media\Domain\Repository\AssetCollectionRepository;
use Neos\Neos\Service\UserService;
use Psr\Log\LoggerInterface;

#[Flow\Scope("singleton")]
class AssetManipulator {

    #[Flow\Inject]
    protected ?AssetCollectionRepository $assetCollectionRepository;

    #[Flow\Inject]
    protected ?UserService $userService;

    #[Flow\Inject]
    protected ?UserRoleService $userRoleService;

    #[Flow\Inject]
    protected ?LoggerInterface $logger;

    public function assetUploaded(Asset $asset, NodeInterface $node, string $propertyName): void {
        $this->addAssetCollectionToAsset($asset);
    }

    public function assetCreated(Asset $asset): void {
        $this->addAssetCollectionToAsset($asset);
    }

    /**
     * Generic method for adding the collection to the asset.
     */
    protected function addAssetCollectionToAsset(Asset $asset): void
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
        $assetCollections = $asset->getAssetCollections();
        if($assetCollections->contains($userAssetCollectionName) === true) {
            return;
        }

        $userAssetCollection->addAsset($asset);
        $this->assetCollectionRepository->update($userAssetCollection);
    }

}
