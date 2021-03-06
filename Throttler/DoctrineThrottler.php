<?php

/*
 * This file is part of the Perimeter package.
 *
 * (c) Adobe Systems, Inc. <bshafs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Perimeter\RateLimitBundle\Throttler;

use Perimeter\RateLimitBundle\Entity\RateLimitBucket;
use Doctrine\ORM\EntityManagerInterface;

class DoctrineThrottler implements ThrottlerInterface
{
    protected $_em;

    protected $isLimitWarning;
    protected $isLimitExceeded;
    protected $bucketSize;
    protected $ratePeriod;
    protected $numBuckets;

    public function __construct(EntityManagerInterface $em, $bucketSize = 60, $numBuckets = 5, $ratePeriod = 3600)
    {
        $this->_em = $em;
        $this->ratePeriod = $ratePeriod;
        $this->bucketSize = $bucketSize;
        $this->numBuckets = $numBuckets;
        $this->ratePeriod = $ratePeriod;
    }

    public function consume($meterId, $warnThreshold, $rateThreshold, $numTokens = 1, $throttleMilliseconds = 0, $time = null)
    {
        list($bucket, $average) = $this->getCurrentBucketAndAverage($meterId, $time);

        if ($bucket) {
            // increment the tokens
            $bucket->tokens += $numTokens;
            $tokens = ($average + $numTokens) * $this->numBuckets;

            if ($tokens > $warnThreshold) {
                $this->isLimitWarning = true;
            }

            if ($tokens > $rateThreshold) {
                $this->isLimitExceeded = true;

                if (!empty($throttleMilliseconds)) {
                    usleep($throttleMilliseconds * 1000);
                }
            }

            $this->_em->persist($bucket);
            $this->_em->flush();
        }
    }

    public function isLimitWarning()
    {
        return (bool) $this->isLimitWarning;
    }

    public function isLimitExceeded()
    {
        return (bool) $this->isLimitExceeded;
    }

    protected function getCurrentBucketAndAverage($meterId, $time = null)
    {
        if (is_null($time)) {
            $time = time();
        }

        /////////// get the sum of all tokens for the past number of buckets

        // current bucket timeBlock
        $timeBlock = $time - ($time % $this->bucketSize);

        // earliest bucket timeBlock
        $earliestTimeBlock = ($timeBlock - ($this->bucketSize * ($this->numBuckets-1)));

        $query = $this->_em->createQuery('SELECT SUM(l.tokens) as total, MAX(l.time_block) as latest
            FROM Perimeter\RateLimitBundle\Entity\RateLimitBucket l 
            WHERE l.meter_id = ?1 AND l.time_block >= ?2
            ORDER BY l.time_block DESC');

        $query->setParameter(1, $meterId)
            ->setParameter(2, $earliestTimeBlock);

        $result = $query->getOneOrNullResult();

        $average = $result['total'] / $this->numBuckets;

        /////////// get or create the current bucket

        // only query for the bucket if it exists
        if ($result['latest'] == $timeBlock) {
            $query = $this->_em->createQuery('SELECT l
                FROM Perimeter\RateLimitBundle\Entity\RateLimitBucket l 
                WHERE l.meter_id = ?1 AND l.time_block = ?2
                ORDER BY l.time_block DESC');

            $query->setParameter(1, $meterId)
                ->setParameter(2, $timeBlock);

            $bucket = $query->getOneOrNullResult();
        } else {
            $bucket = new RateLimitBucket();
            $bucket->meter_id   = $meterId;
            $bucket->time_block = $timeBlock;
        }

        return array($bucket, $average);
    }
}
