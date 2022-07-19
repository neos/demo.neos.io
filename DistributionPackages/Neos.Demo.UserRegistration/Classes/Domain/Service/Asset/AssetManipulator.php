<?php
namespace Neos\Demo\UserRegistration\Domain\Service\Asset;

use Neos\ContentRepository\Domain\Model\NodeInterface;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Persistence\PersistenceManagerInterface;
use Neos\Media\Domain\Model\Asset;
use Neos\Media\Domain\Model\AssetCollection;
use Neos\Media\Domain\Model\AssetInterface;
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
     * @var PolicyService
     */
    protected $policyService;

    /**
     * @Flow\Inject
     * @var PersistenceManagerInterface
     */
    protected $persistenceManager;

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
        if ($this->isRestrictedEditor() === false) {
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


    /**
     * checks if editor has RestrictedEditorRole
     *
     * @return bool
     */
    protected function isRestrictedEditor() {
        $restrictedEditorRole = $this->policyService->getRole('Neos.Neos:RestrictedEditor');
        $userAccounts = $this->userService->getBackendUser()->getAccounts();
        $isRestrictedEditor = false;
        /* @var \Neos\Flow\Security\Account */
        foreach ($userAccounts as $account) {
            if ($account->hasRole($restrictedEditorRole)) {
                $isRestrictedEditor = true;
            }
        }
        return $isRestrictedEditor;
    }
}
