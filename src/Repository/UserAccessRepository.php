<?php

namespace App\Repository;

use App\Entity\UserAccessEntity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UserAccessEntity>
 *
 * @method UserAccessEntity|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserAccessEntity|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserAccessEntity[]    findAll()
 * @method UserAccessEntity[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserAccessRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserAccessEntity::class);
    }

    public function add(UserAccessEntity $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(UserAccessEntity $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return UserAccessEntity[] Returns an array of UserAccessEntity objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('u')
//            ->andWhere('u.exampleField = :val')
//            ->setParaaccount('val', $value)
//            ->orderBy('u.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?UserAccessEntity
//    {
//        return $this->createQueryBuilder('u')
//            ->andWhere('u.exampleField = :val')
//            ->setParaaccount('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
