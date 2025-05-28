<?php
namespace Neos\Demo\UserRegistration;

use Neos\Flow\Core\Bootstrap;
use Neos\Flow\Package\Package as BasePackage;
use Neos\Flow\Security\Authentication\AuthenticationProviderManager;
use Neos\Media\Domain\Service\AssetService;
use Neos\Demo\UserRegistration\Domain\Service\Asset\AssetManipulator;
use Neos\Demo\UserRegistration\Domain\Service\Workspace\WorkspaceService;

class Package extends BasePackage
{
    public function boot(Bootstrap $bootstrap): void
    {
        $dispatcher = $bootstrap->getSignalSlotDispatcher();
        $dispatcher->connect(AssetService::class, 'assetCreated', AssetManipulator::class, 'assetCreated');
        $dispatcher->connect(AuthenticationProviderManager::class, 'successfullyAuthenticated', WorkspaceService::class, 'switchToReviewWorkspace');
    }
}
