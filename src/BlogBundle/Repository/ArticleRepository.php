<?php

namespace BlogBundle\Repository;

/**
 * ArticleRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ArticleRepository extends \Doctrine\ORM\EntityRepository
{
    public function getArticlesByCategory(string $categoryChoice) {
        if($categoryChoice !== "ALL") {
            return $this->createQueryBuilder('articles')
                ->innerJoin('articles.category', 'category')
                ->where('category.name = :categoryName')
                ->setParameter('categoryName', $categoryChoice)
                ->getQuery()
                ->getResult();
        }

        return $this->createQueryBuilder('articles')
            ->innerJoin('articles.category', 'category')
            ->getQuery()
            ->getResult();
    }

    public function getArticlesByPage(string $categoryChoice, int $limit, int $offset){
        if($categoryChoice !== "ALL") {
            return $this->createQueryBuilder('articles')
                ->innerJoin('articles.category', 'category')
                ->where('category.name = :categoryName')
                ->setMaxResults($limit)
                ->setFirstResult($offset)
                ->setParameter('categoryName', $categoryChoice)
                ->getQuery()
                ->getResult();
        }

        return $this->createQueryBuilder('articles')
            ->innerJoin('articles.category', 'category')
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->getQuery()
            ->getResult();
    }
}
