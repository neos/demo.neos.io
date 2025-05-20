<?php

namespace Neos\Demo\UserRegistration\Security\Authorization\Privilege;

/*
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\Media\Security\Authorization\Privilege\ReadAssetPrivilege as MediaReadAssetPrivilege;
use Neos\Media\Domain\Model\AssetCollection;
use Neos\Demo\UserRegistration\Security\Authorization\Privilege\Doctrine\AssetCollectionConditionGenerator;

/**
 * Privilege for restricting reading of Assets
 */
class ReadAssetCollectionPrivilege extends MediaReadAssetPrivilege
{
    /**
     * @param string $entityType
     */
    public function matchesEntityType($entityType): bool
    {
        return $entityType === AssetCollection::class;
    }

    protected function getConditionGenerator(): AssetCollectionConditionGenerator
    {
        return new AssetCollectionConditionGenerator();
    }
}
