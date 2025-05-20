<?php

namespace Neos\Demo\UserRegistration\Domain\Service\Asset;

use Neos\ContentRepository\Core\Projection\ContentGraph\Node;
use Neos\ContentRepository\Core\SharedModel\ContentRepository\ContentRepositoryId;
use Neos\Demo\UserRegistration\Domain\Service\User\UserRoleService;
use Neos\Flow\Annotations as Flow;
use Neos\Media\Domain\Model\Asset;
use Neos\Media\Domain\Model\AssetCollection;
use Neos\Media\Domain\Repository\AssetCollectionRepository;
use Neos\Neos\Domain\Repository\WorkspaceMetadataAndRoleRepository;
use Neos\Neos\Service\UserService;
use Psr\Log\LoggerInterface;

#[Flow\Scope("singleton")]
class AssetManipulator
{

    #[Flow\Inject]
    protected ?AssetCollectionRepository $assetCollectionRepository;

    #[Flow\Inject]
    protected ?UserService $userService;

    #[Flow\Inject]
    protected ?UserRoleService $userRoleService;

    #[Flow\Inject]
    protected WorkspaceMetadataAndRoleRepository $metadataAndRoleRepository;

    #[Flow\Inject]
    protected ?LoggerInterface $logger;

    public function assetUploaded(Asset $asset, Node $node, string $propertyName): void
    {
        $this->addAssetCollectionToAsset($asset);
    }

    public function assetCreated(Asset $asset): void
    {
        $this->addAssetCollectionToAsset($asset);
    }

    /**
     * Generic method for adding the collection to the asset.
     */
    protected function addAssetCollectionToAsset(Asset $asset): void
    {
        $currentUser = $this->userService->getBackendUser();
        if ($currentUser === null) {
            $this->logger->debug("No backend user found, cannot add asset collection.");
            return;
        }

        if ($this->userRoleService->isRestrictedEditor() === false) {
            return;
        }

        // we have no other content repository than default
        $contentRepositoryId = ContentRepositoryId::fromString('default');
        $userAssetCollectionName = $this->metadataAndRoleRepository->findWorkspaceNameByUser($contentRepositoryId, $currentUser->getId());
        $userAssetCollection = $this->assetCollectionRepository->findOneByTitle($userAssetCollectionName);
        if ($userAssetCollection === null) {
            $this->logger->debug("No asset collection found with the title " . $userAssetCollectionName);
            $userAssetCollection = new AssetCollection($userAssetCollectionName);
        }
        $assetCollections = $asset->getAssetCollections();
        if ($assetCollections->contains($userAssetCollectionName) === true) {
            return;
        }

        $userAssetCollection->addAsset($asset);
        $this->assetCollectionRepository->update($userAssetCollection);
    }

}
