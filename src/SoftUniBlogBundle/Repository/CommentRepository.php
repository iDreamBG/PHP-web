<?php

namespace SoftUniBlogBundle\Repository;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping;
use SoftUniBlogBundle\Entity\Article;

class CommentRepository extends \Doctrine\ORM\EntityRepository
{
    public function findAllComments(Article $article) {
        return $this->createQueryBuilder('c')
            ->where('c.article = :article')
            ->setParameter('article', $article)
            ->addOrderBy('c.dateAdded', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
