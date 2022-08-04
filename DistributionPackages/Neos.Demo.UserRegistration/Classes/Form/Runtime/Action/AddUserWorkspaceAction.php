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

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Mvc\ActionResponse;
use Neos\Flow\Persistence\Doctrine\PersistenceManager;
use Neos\Fusion\Form\Runtime\Domain\Exception\ActionException;
use Neos\Neos\Domain\Exception as DomainException;
use Neos\Neos\Domain\Model\User;
use Neos\Neos\Domain\Service\UserService;
use Neos\Neos\Utility\User as UserUtility;
use Neos\Fusion\Form\Runtime\Action\AbstractAction;
use Neos\ContentRepository\Domain\Repository\WorkspaceRepository;
use Neos\ContentRepository\Domain\Model\Workspace;

class AddUserWorkspaceAction extends AbstractAction
{
    #[Flow\Inject]
    protected ?UserService $userService;

    #[Flow\Inject]
    protected ?WorkspaceRepository $workspaceRepository;

    #[Flow\Inject]
    protected ?PersistenceManager $persistenceManager;

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
        $userWorkspaceName = UserUtility::getPersonalWorkspaceNameForUsername($accountIdentifier);
        $privateWorkspaceForUser = $this->workspaceRepository->findOneByName('private-' . $userWorkspaceName);

        if (!$privateWorkspaceForUser) {
            $this->addWorkspaceForUser($userWorkspaceName, $existingUser);
            $this->persistenceManager->persistAll();
        }
        return null;
    }

    /**
     * Adds a workspace for the new user
     * so that he has a sandboxed playground
     */
    protected function addWorkspaceForUser(string $userWorkspaceName, User $user): void
    {
        //create private user workspace
        //@see Neos\Neos\Ui\Controller\BackendServiceController->changeBaseWorkspace()
        $liveWorkspace = $this->workspaceRepository->findOneByName('live');

        $privateWorkspaceNameForUser = 'private-' . $userWorkspaceName;
        $privateWorkspaceForUser = new Workspace(
            $privateWorkspaceNameForUser,
            $liveWorkspace,
            $user
        );
        $privateWorkspaceForUser->setTitle('Review workspace for ' . $user->getLabel());
        $this->workspaceRepository->add($privateWorkspaceForUser);

        $userWorkspace = $this->workspaceRepository->findOneByName($userWorkspaceName);
        if ($userWorkspace === null) {
            $userWorkspace = new Workspace(
                $userWorkspaceName,
                $privateWorkspaceForUser,
                $user
            );

            $this->workspaceRepository->add($userWorkspace);
        } else {
            $userWorkspace->setBaseWorkspace($privateWorkspaceForUser);
            $this->workspaceRepository->update($userWorkspace);
        }
    }
}
