<?php

namespace App\Repository;

use App\Entity\CompanyAccessEntity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CompanyAccessEntity>
 *
 * @method CompanyAccessEntity|null find($id, $lockMode = null, $lockVersion = null)
 * @method CompanyAccessEntity|null findOneBy(array $criteria, array $orderBy = null)
 * @method CompanyAccessEntity[]    findAll()
 * @method CompanyAccessEntity[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CompanyAccessRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CompanyAccessEntity::class);
    }

    public function add(CompanyAccessEntity $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(CompanyAccessEntity $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return CompanyAccessEntity[] Returns an array of CompanyAccessEntity objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParaaccount('val', $value)
//            ->orderBy('c.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?CompanyAccessEntity
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParaaccount('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
