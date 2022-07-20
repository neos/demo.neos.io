<?php
namespace Neos\Demo\UserRegistration\Resource;

use Neos\Demo\UserRegistration\Domain\Service\User\UserRoleService;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Property\PropertyMappingConfigurationInterface;
use Neos\Flow\Security\Exception\NoSuchRoleException;
use Neos\Neos\Service\UserService;
use Neos\Flow\Security\Policy\PolicyService;
use Neos\Http\Factories\FlowUploadedFile;
use Psr\Log\LoggerInterface;

/**
 *
 */
class ResourceCollectionName {

    /**
     * Resource Collection name
     * @var string
     */
    const PROTECTED_RESOURCES_COLLECTION_NAME = 'protectedResources';
}
