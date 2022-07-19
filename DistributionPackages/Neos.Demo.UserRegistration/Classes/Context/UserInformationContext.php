<?php
namespace Neos\Demo\UserRegistration\Context;

use Neos\Flow\Security\Context;
use Neos\Flow\Persistence\PersistenceManagerInterface;
use Neos\Cache\CacheAwareInterface;
use Neos\Flow\Annotations as Flow;

/**
 * @see https://www.neos.io/blog/flow-bugfix-releases-for-entity-security.html
 * Flow uses only roles to cache doctrine queries, thus results are not really
 * user based, but same roles see same results, even if just cached.
 * Packages/Framework/Neos.Flow/Classes/Security/Context.php:854
 *
 * @Flow\Scope("singleton")
 */
class UserInformationContext implements CacheAwareInterface {
    /**
     * @Flow\Inject
     * @var Context
     */
    protected $securityContext;

    /**
     * @Flow\Inject
     * @var \Neos\Flow\Persistence\PersistenceManagerInterface
     */
    protected $persistenceManager;

    /**
     * @return string
     */
    public function getCacheEntryIdentifier(): string {
        if ($this->securityContext->getAccount() !== null) {
            return $this->securityContext->getAccount()->getAccountIdentifier();
        }
        return '';
    }
}
