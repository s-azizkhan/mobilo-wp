<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CheckoutWC\Symfony\Component\Lock\Store;

use CheckoutWC\Psr\Log\LoggerAwareInterface;
use CheckoutWC\Psr\Log\LoggerAwareTrait;
use CheckoutWC\Psr\Log\NullLogger;
use CheckoutWC\Symfony\Component\Lock\BlockingStoreInterface;
use CheckoutWC\Symfony\Component\Lock\Exception\LockConflictedException;
use CheckoutWC\Symfony\Component\Lock\Key;
use CheckoutWC\Symfony\Component\Lock\Lock;
use CheckoutWC\Symfony\Component\Lock\PersistingStoreInterface;

checkoutwc_trigger_deprecation('symfony/lock', '5.2', '%s is deprecated, the "%s" class provides the logic when store is not blocking.', RetryTillSaveStore::class, Lock::class);

/**
 * RetryTillSaveStore is a PersistingStoreInterface implementation which decorate a non blocking PersistingStoreInterface to provide a
 * blocking storage.
 *
 * @author Jérémy Derussé <jeremy@derusse.com>
 *
 * @deprecated since Symfony 5.2
 */
class RetryTillSaveStore implements BlockingStoreInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    private $decorated;
    private $retrySleep;
    private $retryCount;

    /**
     * @param int $retrySleep Duration in ms between 2 retry
     * @param int $retryCount Maximum amount of retry
     */
    public function __construct(PersistingStoreInterface $decorated, int $retrySleep = 100, int $retryCount = \PHP_INT_MAX)
    {
        $this->decorated = $decorated;
        $this->retrySleep = $retrySleep;
        $this->retryCount = $retryCount;

        $this->logger = new NullLogger();
    }

    /**
     * {@inheritdoc}
     */
    public function save(Key $key)
    {
        $this->decorated->save($key);
    }

    /**
     * {@inheritdoc}
     */
    public function waitAndSave(Key $key)
    {
        $retry = 0;
        $sleepRandomness = (int) ($this->retrySleep / 10);
        do {
            try {
                $this->decorated->save($key);

                return;
            } catch (LockConflictedException $e) {
                usleep(($this->retrySleep + random_int(-$sleepRandomness, $sleepRandomness)) * 1000);
            }
        } while (++$retry < $this->retryCount);

        $this->logger->warning('Failed to store the "{resource}" lock. Abort after {retry} retry.', ['resource' => $key, 'retry' => $retry]);

        throw new LockConflictedException();
    }

    /**
     * {@inheritdoc}
     */
    public function putOffExpiration(Key $key, float $ttl)
    {
        $this->decorated->putOffExpiration($key, $ttl);
    }

    /**
     * {@inheritdoc}
     */
    public function delete(Key $key)
    {
        $this->decorated->delete($key);
    }

    /**
     * {@inheritdoc}
     */
    public function exists(Key $key)
    {
        return $this->decorated->exists($key);
    }
}
