<?php

namespace Neos\Demo\UserRegistration\Security\Authorization\Privilege\Doctrine;

/*
 * This file is part of the Neos.Media package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\Flow\Security\Authorization\Privilege\Entity\Doctrine\PropertyConditionGenerator;
use Neos\Flow\Security\Exception\InvalidPrivilegeException;
use Neos\Media\Domain\Model\AssetCollection;

/**
 * A SQL condition generator, supporting special SQL constraints for asset collections
 */
class AssetCollectionConditionGenerator extends AbstractConditionGenerator
{
    /**
     * @var string
     */
    protected $entityType = AssetCollection::class;

    /**
     * @param string $entityType
     * @return boolean
     * @throws InvalidPrivilegeException
     */
    public function isType($entityType)
    {
        throw new InvalidPrivilegeException('The isType() operator must not be used in AssetCollection privilege matchers!', 1747742810);
    }

    /**
     * @param string $contextPathForAccount
     * @return PropertyConditionGenerator
     */
    public function isTitledByUserWorkspaceName($contextPathForAccount)
    {
        $title = $this->getWorkSpaceNameFromUserContext($contextPathForAccount);
        $propertyConditionGenerator = new PropertyConditionGenerator('title');
        return $propertyConditionGenerator->equals($title);
    }
}
