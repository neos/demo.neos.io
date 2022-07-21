<?php
namespace Neos\Demo\UserRegistration;

use Neos\Demo\UserRegistration\Domain\Service\Asset\AssetManipulator;
use Neos\Flow\Core\Bootstrap;
use Neos\Flow\Package\Package as BasePackage;
use Neos\Media\Domain\Service\AssetService;

class Package extends BasePackage
{
    public function boot(Bootstrap $bootstrap): void
    {
        $dispatcher = $bootstrap->getSignalSlotDispatcher();
        $dispatcher->connect(AssetService::class, 'assetCreated', AssetManipulator::class, 'assetCreated');
    }
}
