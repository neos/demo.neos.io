<?php
namespace Neos\Demo\UserRegistration\Aspects;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Property\PropertyMappingConfigurationInterface;
use Neos\Flow\Security\Exception\NoSuchRoleException;
use Neos\Neos\Service\UserService;
use Neos\Flow\Security\Policy\PolicyService;
use Neos\Http\Factories\FlowUploadedFile;
use Psr\Log\LoggerInterface;

/**
 *
 * @Flow\Aspect
 */
class ModifyResourceCollectionNameAspect {

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
     * @Flow\Inject
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Resource Collection name
     * @var string
     */
    const PROTECTED_RESOURCES_COLLECTION_NAME = 'protectedResources';

    /**
     * @Flow\Before("method(Neos\Flow\ResourceManagement\ResourceTypeConverter->getCollectionName())")
     * @return void
     */
    public function modifyCollectionNameBasedOnRole(\Neos\Flow\AOP\JoinPointInterface $joinPoint) {
        if ($this->isRestrictedEditor()) {
            $source = $joinPoint->getMethodArgument('source');
            /* @var $source FlowUploadedFile */
            if (!$source->getCollectionName()) {
                $source->setCollectionName(self::PROTECTED_RESOURCES_COLLECTION_NAME);
                $joinPoint->setMethodArgument('source', $source);
            }
        }
    }

    /**
     * checks if editor has RestrictedEditorRole
     *
     * @return bool
     */
    protected function isRestrictedEditor() {
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
