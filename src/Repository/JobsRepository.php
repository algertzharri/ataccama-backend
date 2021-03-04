<?php

namespace App\Repository;

use App\Entity\Jobs;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Jobs|null find($id, $lockMode = null, $lockVersion = null)
 * @method Jobs|null findOneBy(array $criteria, array $orderBy = null)
 * @method Jobs[]    findAll()
 * @method Jobs[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class JobsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Jobs::class);
    }

    /**
     * @param Jobs $jobs
     * @return array
     */
    public function transform(Jobs $jobs): array
    {
        return [
            'id' => (int)$jobs->getId(),
            'title' => (string)$jobs->getTitle(),
            'slug' => (string)$jobs->getSlug(),
            'is_active' => (int)$jobs->getIsActive(),
            'content' => (string)$jobs->getContent(),
            'location' => (string)$jobs->getLocation()
        ];
    }

    /**
     * @return array
     */
    public function findByActive(): array
    {
        $activeJobs = $this->activeJobs();
        $jobsArray = [];

        foreach ($activeJobs as $job)
        {
            $jobsArray[] = $this->transform($job);
        }

        return $jobsArray;
    }

    private function activeJobs()
    {
        return  $this->createQueryBuilder('job')
            ->andWhere('job.isActive = :value')
            ->setParameter('value', 1)
            ->orderBy('job.id', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }
}
