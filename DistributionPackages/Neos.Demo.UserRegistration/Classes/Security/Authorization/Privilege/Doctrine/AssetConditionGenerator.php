<?php

namespace Neos\Demo\UserRegistration\Security\Authorization\Privilege\Doctrine;

/*
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\Media\Domain\Model\Asset;
use Neos\Media\Security\Authorization\Privilege\Doctrine\AssetAssetCollectionConditionGenerator;

/**
 * A SQL condition generator, supporting special SQL constraints for assets
 */
class AssetConditionGenerator extends AbstractConditionGenerator
{
    /**
     * @var string
     */
    protected $entityType = Asset::class;

    /**
     * @param string $contextPathForAccount
     * @return AssetAssetCollectionConditionGenerator
     */
    public function isInCollection($contextPathForAccount)
    {
        $title = $this->getWorkSpaceNameFromUserContext($contextPathForAccount);
        return new AssetAssetCollectionConditionGenerator($title);
    }

    /**
     * This privilege method should check for any Asset that is
     * - either without collection
     * - or outside a certain collection
     *
     * Problem is that both conditions cannot be combined, so this is something like standard
     * isWithoutCollection() || !isInCollection('abc')
     *
     * This is being used for user-based collections.
     * A user should have his own collection not accessible by other users (except e.g. Administrators).
     * A rare usecase where users only work in Backend and have no access to live
     */
    public function isWithoutCollectionOrOutsideOfCollection(string $contextPathForAccount): AssetWithoutAssetCollectionOrTitledConditionGenerator
    {
        $title = $this->getWorkSpaceNameFromUserContext($contextPathForAccount);
        return new AssetWithoutAssetCollectionOrTitledConditionGenerator($title);
    }
}
