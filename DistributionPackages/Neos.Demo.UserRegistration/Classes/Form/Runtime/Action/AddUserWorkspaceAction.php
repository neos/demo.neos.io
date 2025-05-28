<?php
declare(strict_types=1);

namespace Neos\Demo\UserRegistration\Form\Runtime\Action;

/*
 * This file is part of the Neos.Fusion.Form package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\ContentRepository\Core\SharedModel\ContentRepository\ContentRepositoryId;
use Neos\ContentRepository\Core\SharedModel\Workspace\WorkspaceName;
use Neos\Demo\UserRegistration\Domain\Service\Workspace\WorkspaceService;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Mvc\ActionResponse;
use Neos\Flow\Persistence\Doctrine\PersistenceManager;
use Neos\Fusion\Form\Runtime\Domain\Exception\ActionException;
use Neos\Neos\Domain\Exception as DomainException;
use Neos\Neos\Domain\Service\UserService;
use Neos\Fusion\Form\Runtime\Action\AbstractAction;

class AddUserWorkspaceAction extends AbstractAction
{
    #[Flow\Inject]
    protected ?UserService $userService;

    #[Flow\Inject]
    protected ?PersistenceManager $persistenceManager;

    #[Flow\Inject]
    protected WorkspaceService $workspaceService;

    /**
     * @throws ActionException|DomainException
     */
    public function perform(): ?ActionResponse
    {
        $accountIdentifier =  $this->options['username'];
        $existingUser = $this->userService->getUser($accountIdentifier, 'Neos.Neos:Backend');
        if ($existingUser === null) {
            throw new ActionException('User not found, cannot add workspace.');
        }

        $this->workspaceService->createPersonalWorkspace($existingUser, WorkspaceName::forLive());
        $this->workspaceService->createReviewWorkspaceIfNotExists($existingUser);
        $this->persistenceManager->persistAll();

        return null;
    }
}
