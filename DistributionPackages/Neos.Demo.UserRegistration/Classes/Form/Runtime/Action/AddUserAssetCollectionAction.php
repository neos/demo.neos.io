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
use Neos\Fusion\Form\Runtime\Action\AbstractAction;
use Neos\Fusion\Form\Runtime\Domain\Exception\ActionException;
use Neos\Media\Domain\Model\AssetCollection;
use Neos\Media\Domain\Repository\AssetCollectionRepository;
use Neos\Neos\Domain\Exception as DomainException;
use Neos\Neos\Domain\Service\UserService;
use Neos\Neos\Utility\User as UserUtility;

class AddUserAssetCollectionAction extends AbstractAction
{
    #[Flow\Inject]
    protected ?UserService $userService;

    #[Flow\Inject]
    protected ?AssetCollectionRepository $assetCollectionRepository;

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
            throw new ActionException('User not found, cannot add collection.');
        }
        $userWorkspaceName = UserUtility::getPersonalWorkspaceNameForUsername($accountIdentifier);
        $this->addAssetCollectionForUser($userWorkspaceName);
        $this->persistenceManager->persistAll();
        return null;
    }

    /**
     * Adds a workspace for the new user so that he has a sandboxed playground
     */
    protected function addAssetCollectionForUser(string $userWorkspaceName): void {
        $existingAssetCollection = $this->assetCollectionRepository->findOneByTitle($userWorkspaceName);
        if ($existingAssetCollection === null) {
            $this->assetCollectionRepository->add(new AssetCollection($userWorkspaceName));
        }
    }

}
