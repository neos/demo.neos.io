<?php

namespace Neos\Demo\UserRegistration\Security\Authorization\Privilege\Doctrine;

/*
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\Flow\Annotations as Flow;
use Exception;
use Neos\ContentRepository\Core\SharedModel\ContentRepository\ContentRepositoryId;
use Neos\Flow\Security\Account;
use Neos\Flow\Security\Authorization\Privilege\Entity\Doctrine\ConditionGenerator as EntityConditionGenerator;
use Neos\Flow\Security\Authorization\Privilege\Entity\Doctrine\PropertyConditionGenerator;
use Neos\Neos\Domain\Repository\WorkspaceMetadataAndRoleRepository;
use Neos\Neos\Domain\Service\UserService;

/**
 * A SQL condition generator, supporting special SQL constraints for assets
 */
abstract class AbstractConditionGenerator extends EntityConditionGenerator
{
    #[Flow\Inject]
    protected WorkspaceMetadataAndRoleRepository $metadataAndRoleRepository;

    #[Flow\Inject]
    protected ?UserService $userService;

    /**
     * Returns the workspace name for the given user context. The context path for teh account is something
     * like "context.securityContext.account"
     *
     * @param $contextPathForAccount
     * @return string
     */
    protected function getWorkSpaceNameFromUserContext($contextPathForAccount): string
    {
        try {
            $propertyConditionGenerator = new PropertyConditionGenerator('');
            /** @var Account $account */
            $account = $propertyConditionGenerator->getValueForOperand($contextPathForAccount);
            $accountIdentifier = $account->getAccountIdentifier();

            $existingUser = $this->userService->getUser($accountIdentifier, 'Neos.Neos:Backend');
            $contentRepositoryId = ContentRepositoryId::fromString('default');
            $userWorkspaceName = $this->metadataAndRoleRepository->findWorkspaceNameByUser($contentRepositoryId, $existingUser->getId());

            return !$userWorkspaceName ? '' : $userWorkspaceName->value;
        } catch (Exception) {
            return '';
        }
    }
}
