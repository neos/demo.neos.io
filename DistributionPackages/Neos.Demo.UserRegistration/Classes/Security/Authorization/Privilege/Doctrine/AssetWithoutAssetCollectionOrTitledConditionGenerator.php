<?php
namespace Neos\Demo\UserRegistration\Security\Authorization\Privilege\Doctrine;

/*
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Doctrine\Persistence\Mapping\ClassMetadata;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Filter\SQLFilter as DoctrineSqlFilter;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Security\Authorization\Privilege\Entity\Doctrine\PropertyConditionGenerator;
use Neos\Flow\Security\Authorization\Privilege\Entity\Doctrine\SqlGeneratorInterface;

/**
 * Condition generator covering Asset <-> AssetCollection relations (M:M relations are not supported by the Flow
 * PropertyConditionGenerator yet)
 */
class AssetWithoutAssetCollectionOrTitledConditionGenerator implements SqlGeneratorInterface
{
    /**
     * @Flow\Inject
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var string
     */
    protected $collectionTitle;

    public function __construct(string $collectionTitle)
    {
        $this->collectionTitle = $collectionTitle;
    }

    /**
     * @param ClassMetadata $targetEntity Metadata object for the target entity to create the constraint for
     * @param string $targetTableAlias The target table alias used in the current query
     */
    public function getSql(DoctrineSqlFilter $sqlFilter, ClassMetadata $targetEntity, $targetTableAlias): string
    {
        $propertyConditionGenerator = new PropertyConditionGenerator('');
        $collectionTitle = $propertyConditionGenerator->getValueForOperand($this->collectionTitle);
        $whereCondition = $targetTableAlias . '_acj.media_asset IS NOT NULL AND ' . $targetTableAlias . '_ac.title != ' . $this->entityManager->getConnection()->quote($collectionTitle);


        return $targetTableAlias . '.persistence_object_identifier IN (
            SELECT ' . $targetTableAlias . '_a.persistence_object_identifier
            FROM neos_media_domain_model_asset AS ' . $targetTableAlias . '_a
            LEFT JOIN neos_media_domain_model_assetcollection_assets_join ' . $targetTableAlias . '_acj ON ' . $targetTableAlias . '_a.persistence_object_identifier = ' . $targetTableAlias . '_acj.media_asset
            LEFT JOIN neos_media_domain_model_assetcollection ' . $targetTableAlias . '_ac ON ' . $targetTableAlias . '_ac.persistence_object_identifier = ' . $targetTableAlias . '_acj.media_assetcollection
            WHERE ' . $whereCondition . ')';
    }
}
