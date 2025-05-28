<?php

declare(strict_types=1);

namespace Neos\Demo\UserRegistration\Domain\Service\Workspace;

use Neos\Flow\Annotations as Flow;
use Neos\ContentRepository\Core\Feature\WorkspaceCommandSkipped;
use Neos\ContentRepository\Core\Feature\WorkspaceModification\Command\ChangeBaseWorkspace;
use Neos\ContentRepository\Core\SharedModel\Exception\WorkspaceDoesNotExist;
use Neos\ContentRepository\Core\SharedModel\Workspace\Workspace;
use Neos\ContentRepository\Core\Feature\WorkspaceCreation\Exception\WorkspaceAlreadyExists;
use Neos\ContentRepository\Core\SharedModel\ContentRepository\ContentRepositoryId;
use Neos\ContentRepository\Core\SharedModel\Workspace\WorkspaceName;
use Neos\ContentRepositoryRegistry\ContentRepositoryRegistry;
use Neos\Flow\Log\Utility\LogEnvironment;
use Neos\Neos\Domain\Model\User;
use Neos\Neos\Domain\Model\WorkspaceDescription;
use Neos\Neos\Domain\Model\WorkspaceRoleAssignments;
use Neos\Neos\Domain\Model\WorkspaceTitle;
use Neos\Neos\Domain\Repository\WorkspaceMetadataAndRoleRepository;
use Neos\Neos\Domain\Service\UserService;
use Neos\Neos\Domain\Service\WorkspaceService as NeosWorkspaceService;
use Psr\Log\LoggerInterface;

#[Flow\Scope("singleton")]
class WorkspaceService
{
    #[Flow\InjectConfiguration(path: 'reviewWorkspace', package: 'Neos.Demo.UserRegistration')]
    protected ?array $workspaceSettings;

    #[Flow\Inject]
    protected ContentRepositoryRegistry $contentRepositoryRegistry;

    #[Flow\Inject]
    protected NeosWorkspaceService $workspaceService;

    #[Flow\Inject]
    protected UserService $userService;

    #[Flow\Inject]
    protected WorkspaceMetadataAndRoleRepository $metadataAndRoleRepository;

    #[Flow\Inject]
    protected LoggerInterface $systemLogger;

    public function switchToReviewWorkspace(): void
    {
        $user = $this->userService->getCurrentUser();

        if ($user === null) {
            return;
        }

        if (!$this->workspaceSettings['changeBaseWorkspace']) {
            return;
        }

        $currentUserRoles = $this->userService->getAllRoles($user);
        if (array_key_exists('Neos.Neos:RestrictedEditor', $currentUserRoles)) {
            // Change the base workspace to the review workspace
            $contentRepositoryId = $this->getContentRepositoryId();
            $title = $this->getReviewWorkspaceTitle($user);
            $reviewWorkspaceName = WorkspaceName::transliterateFromString($title->value);
            try {
                $contentRepository = $this->contentRepositoryRegistry->get($contentRepositoryId);
                $personalWorkspaceName = $this->metadataAndRoleRepository->findWorkspaceNameByUser($contentRepositoryId, $user->getId());
                if ($personalWorkspaceName === null) {
                    $this->createPersonalWorkspace($user, $reviewWorkspaceName);
                    $this->systemLogger->error(
                        'Personal workspace did not exist yet, so we created one with review workspace as base for user: ' . $user->getLabel(),
                        LogEnvironment::fromMethodName(__METHOD__)
                    );
                    return;
                }
                $personalWorkspace = $this->getWorkspaceByName($personalWorkspaceName);

                // change base workspace when it is targeting the live workspace
                if ($personalWorkspace->baseWorkspaceName->equals(WorkspaceName::forLive())) {
                    $contentRepository->handle(
                        ChangeBaseWorkspace::create(
                            $personalWorkspaceName,
                            $reviewWorkspaceName,
                        )
                    );
                }
            } catch (WorkspaceCommandSkipped $exception) {
                // The workspace already has the review workspace as base workspace, so we do nothing
                $this->systemLogger->debug(
                    sprintf('Workspace "%s" already has "%s" as base workspace.', $personalWorkspaceName->value, $reviewWorkspaceName->value),
                    LogEnvironment::fromMethodName(__METHOD__)
                );
            } catch (\Exception $exception) {
                $this->systemLogger->error(
                    'Could not change base workspace: ' . $exception->getMessage(),
                    LogEnvironment::fromMethodName(__METHOD__)
                );
            }
        }
    }

    /**
     * Creates a review workspace for the given user if it does not already exist.
     * If we have no user, we try to get the current user from the UserService.
     *
     * @param User|null $user
     * @return void
     */
    public function createReviewWorkspaceIfNotExists(?User $user): void
    {
        if (!($user instanceof User)) {
            $user = $this->userService->getCurrentUser();
        }

        if ($user === null) {
            return;
        }

        $this->createReviewWorkspace($this->getContentRepositoryId(), $user);
    }

    /**
     * Creates a personal workspace for the given user if it does not already exist.
     * We also set the review workspace as base workspace.
     *
     * @param User $user
     * @param WorkspaceName $baseName
     * @return void
     */
    public function createPersonalWorkspace(User $user, WorkspaceName $baseName): void
    {
        $contentRepositoryId = $this->getContentRepositoryId();
        $workspaceName = $this->workspaceService->getUniqueWorkspaceName($contentRepositoryId, $user->getLabel());
        $this->workspaceService->createPersonalWorkspace(
            $contentRepositoryId,
            $workspaceName,
            WorkspaceTitle::fromString($user->getLabel()),
            WorkspaceDescription::createEmpty(),
            $baseName,
            $user->getId(),
        );
    }

    /**
     * Creates a review workspace for the given user if it does not already exist.
     *
     * @param ContentRepositoryId $contentRepositoryId
     * @param User $user
     * @return void
     */
    protected function createReviewWorkspace(ContentRepositoryId $contentRepositoryId, User $user): void
    {
        $title = $this->getReviewWorkspaceTitle($user);
        $workspaceName = WorkspaceName::transliterateFromString($title->value);
        $contentRepository = $this->contentRepositoryRegistry->get($contentRepositoryId);
        $existingWorkspaceName = $contentRepository->findWorkspaceByName($workspaceName);
        if ($existingWorkspaceName !== null) {
            return;
        }

        try {
            $this->workspaceService->createSharedWorkspace(
                $contentRepositoryId,
                $workspaceName,
                $title,
                WorkspaceDescription::createEmpty(),
                WorkspaceName::forLive(),
                WorkspaceRoleAssignments::createForPrivateWorkspace($user->getId())
            );
            $this->systemLogger->info(
                sprintf('Workspace "%s" created for user "%s" in content repository "%s".', $workspaceName->value, $user->getLabel(), $contentRepositoryId->value),
                LogEnvironment::fromMethodName(__METHOD__)
            );
        } catch (WorkspaceAlreadyExists $exception) {
            // do nothing, workspace already exists
        } catch (\Exception $exception) {
            $this->systemLogger->error(
                'Workspace could not be created: ' . $exception->getMessage(),
                LogEnvironment::fromMethodName(__METHOD__)
            );
        }
    }

    /**
     * Returns the title for the review workspace based on the user's username.
     *
     * @param User $user
     * @return WorkspaceTitle
     */
    public function getReviewWorkspaceTitle(User $user): WorkspaceTitle
    {
        $username = $this->userService->getUsername($user);
        $title = sprintf($this->workspaceSettings['workspaceNameFormat'], $username);
        return WorkspaceTitle::fromString($title);
    }

    /**
     * Returns workspace by the given name in the given content repository.
     *
     * @throws WorkspaceDoesNotExist if the workspace does not exist
     */
    protected function getWorkspaceByName(WorkspaceName $workspaceName): Workspace
    {
        $contentRepositoryId = $this->getContentRepositoryId();
        $workspace = $this->contentRepositoryRegistry
            ->get($contentRepositoryId)
            ->findWorkspaceByName($workspaceName);
        if ($workspace === null) {
            throw WorkspaceDoesNotExist::butWasSupposedToInContentRepository($workspaceName, $contentRepositoryId);
        }
        return $workspace;
    }

    /**
     * @return ContentRepositoryId
     */
    protected function getContentRepositoryId(): ContentRepositoryId
    {
        // Assuming 'default' is the content repository ID, adjust if necessary
        return ContentRepositoryId::fromString('default');
    }
}
