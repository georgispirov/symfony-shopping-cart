<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Categories;
use AppBundle\Entity\Product;
use AppBundle\Entity\Promotion;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * PromotionRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class PromotionRepository extends EntityRepository implements IPromotionRepository
{
    public function invokeFindByBuilder(): QueryBuilder
    {
        return $this->createQueryBuilder($this->getClassMetadata()->getTableName());
    }

    /**
     * @param Promotion $promotion
     * @return bool
     */
    public function addPromotionForProducts(Promotion $promotion): bool
    {
        $em = $this->getEntityManager();
        $promotion->setCategory(null);
        $em->persist($promotion);

        if (true === $em->getUnitOfWork()->isScheduledForInsert($promotion)) {
            $em->flush();
            return true;
        }

        return false;
    }

    /**
     * @return array
     */
    public function getActivePromotions(): array
    {
        return $this->getEntityManager()
                    ->getRepository(Promotion::class)
                    ->findAll();
    }

    /**
     * @return array
     */
    public function getAllPromotions(): array
    {
        // TODO: Implement getAllPromotions() method.
    }

    /**
     * @return null|Promotion
     */
    public function getPromotionByInterval()
    {
        // TODO: Implement getPromotionByInterval() method.
    }

    /**
     * @param int $promotionID
     * @return null|Promotion
     */
    public function getPromotionByID(int $promotionID)
    {
        $stmt = $this->getEntityManager()
                     ->getRepository(Promotion::class)
                     ->createQueryBuilder('promotion')
                     ->where('promotion.id = :promotionID')
                     ->setParameter('promotionID', $promotionID);

        return $stmt->getQuery()
                    ->getOneOrNullResult();
    }

    /**
     * @param Promotion $promotion
     * @param Product[] $products
     * @return bool
     */
    public function removePromotionFromProducts(Promotion $promotion,
                                                array $products): bool
    {
        $em = $this->getEntityManager();

        /* @var Product $product */
        foreach ($products as $product) {
            $product->removePromotionFromProduct($promotion);
        }

        $em->remove($promotion);

        if (true === $em->getUnitOfWork()->isScheduledForDelete($promotion)) {
            $em->flush();
            return true;
        }

        return false;
    }

    /**
     * @param Promotion $promotion
     * @param Product[] $products
     * @return bool
     */
    public function applyExistingPromotionOnProducts(Promotion $promotion,
                                                     array $products): bool
    {
        $em = $this->getEntityManager();
        $promotion->setCategory(null);

        foreach ($products as $product) { /* @var Product $product */
            $product->addPromotionToProduct($promotion);
        }
        $em->flush();
        return true;
    }

    /**
     * @param Promotion $promotion
     * @param Categories $categories
     * @param array $products
     * @return bool
     */
    public function applyPromotionOnCategory(Promotion $promotion,
                                             Categories $categories,
                                             array $products): bool
    {
        $em = $this->getEntityManager();

        $categories->addPromotionsToCategory($promotion);

        foreach ($products as $product) { /* @var Product $product */
            $product->addPromotionToProduct($promotion);
        }
        $em->persist($promotion);

        if (true === $em->getUnitOfWork()->isEntityScheduled($promotion)) {
            $em->flush();
            return true;
        }

        return false;
    }

    public function removePromotionWithoutProducts(Promotion $promotion): bool
    {
        $em = $this->getEntityManager();
        $em->remove($promotion);
        $em->getUnitOfWork()->scheduleForDelete($promotion);

        if (true === $em->getUnitOfWork()->isScheduledForDelete($promotion)) {
            $em->flush();
            return true;
        }

        return false;
    }
}
