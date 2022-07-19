<?php
namespace Neos\Demo\UserRegistration;

use Neos\Demo\UserRegistration\Domain\Service\Asset\AssetManipulator;
use Neos\Flow\Core\Bootstrap;
use Neos\Flow\Package\Package as BasePackage;
use Neos\Neos\Controller\Backend\ContentController;

class Package extends BasePackage
{
    public function boot(Bootstrap $bootstrap)
    {
        $dispatcher = $bootstrap->getSignalSlotDispatcher();
        $dispatcher->connect(ContentController::class, 'assetUploaded', AssetManipulator::class, 'addAssetCollectionToAsset');
    }
}
