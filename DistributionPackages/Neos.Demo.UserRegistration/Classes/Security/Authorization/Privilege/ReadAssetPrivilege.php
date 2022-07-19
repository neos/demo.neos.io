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
use Neos\Media\Domain\Model\Asset;
use Neos\Demo\UserRegistration\Security\Authorization\Privilege\Doctrine\AssetConditionGenerator;

/**
 * Privilege for restricting reading of Assets
 */
class ReadAssetPrivilege extends MediaReadAssetPrivilege
{
    /**
     * @param string $entityType
     * @return boolean
     */
    public function matchesEntityType($entityType)
    {
        return $entityType === Asset::class;
    }

    /**
     * @return AssetConditionGenerator
     */
    protected function getConditionGenerator()
    {
        return new AssetConditionGenerator();
    }
}
