<?php

namespace App\Repository;

use App\Entity\Book;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Book|null find($id, $lockMode = null, $lockVersion = null)
 * @method Book|null findOneBy(array $criteria, array $orderBy = null)
 * @method Book[]    findAll()
 * @method Book[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BookRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Book::class);
    }

    /**
     * @return Book[] Returns an array of Book objects
     */
    
    public function findByParams($array)
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.title LIKE :title')
            ->setParameter('title', $array['title'])
            ->andWhere('b.description LIKE :descrition')
            ->setParameter('description', $array['description'])
            ->andWhere('b.public_at LIKE :date')
            ->setParameter('date', $array['date'])
            ->orderBy('b.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return Book[] Returns an array of Book objects
     */
    
    public function findByTitle($value)
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.title = :val')
            ->setParameter('val', $value)
            ->orderBy('b.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return Book[] Returns an array of Book objects
     */
    
    public function findByPublicAt($value)
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.publicAt = :val')
            ->setParameter('val', $value)
            ->orderBy('b.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return Book[] Returns an array of Book objects
     */
    
    public function findByDescription($value)
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.description = :val')
            ->setParameter('val', $value)
            ->orderBy('b.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    

    public function findWhereMoreThanTwoAuthors()
    {
        return $this->createQueryBuilder('t1')
            ->leftJoin('t1.authors', 'a')
            ->groupBy('t1.id')
            ->having('COUNT(t1.id) >= 2')
            ->getQuery()
            ->getResult();
    }

    public function findWhereMoreThanTwoAuthorsSQL()
    {
        $conn = $this->getEntityManager()->getConnection();

        $query = 'SELECT book.* 
            FROM book 
            RIGHT JOIN (SELECT book_id 
                        FROM author_book 
                        GROUP BY book_id 
                        HAVING count(author_id) >= 2) t 
            ON book.id=t.book_id';

        $stmt = $conn->prepare($query);
        $stmt->execute();

        // returns an array of arrays (i.e. a raw data set)
        return $stmt->fetchAllAssociative();
    }

    /*
    public function findOneBySomeField($value): ?Book
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}