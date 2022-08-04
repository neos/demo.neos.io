<?php
namespace Neos\Demo\UserRegistration\Domain\Service\User;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Security\Policy\PolicyService;
use Neos\Neos\Service\UserService;

#[Flow\Scope("singleton")]
class UserRoleService {

    #[Flow\Inject]
    protected ?UserService $userService;

    #[Flow\Inject]
    protected ?PolicyService $policyService;

    /**
     * checks if editor has RestrictedEditorRole
     */
    public function isRestrictedEditor(): bool {
        $restrictedEditorRole = $this->policyService->getRole('Neos.Neos:RestrictedEditor');
        $currentUser = $this->userService->getBackendUser();
        if (!$currentUser) {
            return false;
        }
        foreach ($currentUser->getAccounts() as $account) {
            if ($account->hasRole($restrictedEditorRole)) {
                return true;
            }
        }
        return false;
    }
}
