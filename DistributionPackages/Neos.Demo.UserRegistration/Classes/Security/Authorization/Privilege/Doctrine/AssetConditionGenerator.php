<?php
namespace Neos\Demo\UserRegistration\Security\Authorization\Privilege\Doctrine;

/*
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\Flow\Security\Authorization\Privilege\Entity\Doctrine\ConditionGenerator as EntityConditionGenerator;
use Neos\Media\Domain\Model\Asset;

/**
 * A SQL condition generator, supporting special SQL constraints for assets
 */
class AssetConditionGenerator extends EntityConditionGenerator
{
    /**
     * @var string
     */
    protected $entityType = Asset::class;

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
    public function isWithoutCollectionOrOutsideOfCollection(string $collectionTitle): AssetWithoutAssetCollectionOrTitledConditionGenerator
    {
        return new AssetWithoutAssetCollectionOrTitledConditionGenerator($collectionTitle);
    }
}
