<?php
namespace Neos\Demo\UserRegistration\Domain\Service\User;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Property\PropertyMappingConfigurationInterface;
use Neos\Flow\Security\Exception\NoSuchRoleException;
use Neos\Neos\Service\UserService;
use Neos\Flow\Security\Policy\PolicyService;
use Neos\Http\Factories\FlowUploadedFile;
use Psr\Log\LoggerInterface;

/**
 *
 * @Flow\Scope("singleton")
 */
class UserRoleService {

    /**
     * @Flow\Inject
     * @var UserService
     */
    protected $userService;

    /**
     * @Flow\Inject
     * @var PolicyService
     */
    protected $policyService;

    /**
     * checks if editor has RestrictedEditorRole
     *
     * @return bool
     */
    public function isRestrictedEditor() {
        $restrictedEditorRole = $this->policyService->getRole('Neos.Neos:RestrictedEditor');
        $userAccounts = $this->userService->getBackendUser()->getAccounts();
        $isRestrictedEditor = false;
        /* @var \Neos\Flow\Security\Account */
        foreach ($userAccounts as $account) {
            if ($account->hasRole($restrictedEditorRole)) {
                return true;
            }
        }
        return $isRestrictedEditor;
    }
}
