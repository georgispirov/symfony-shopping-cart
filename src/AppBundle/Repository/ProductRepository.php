<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Categories;
use AppBundle\Entity\OrderedProducts;
use AppBundle\Entity\Product;
use AppBundle\Entity\Promotion;
use AppBundle\Entity\User;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * ProductRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ProductRepository extends EntityRepository implements IProductRepository
{
    public function invokeFindByBuilder(): QueryBuilder
    {
        return $this->createQueryBuilder($this->getClassMetadata()->getTableName());
    }

    /**
     * @param string $name
     * @return array
     */
    public function getProductsByCategory(string $name): array
    {
        /* @var Categories $category */
        $category = $this->getEntityManager()
                         ->getRepository(Categories::class)
                         ->findByCategoryName($name);

        $query = $this->getEntityManager()
                      ->getRepository(Product::class)
                      ->createQueryBuilder('product')
                      ->where('product.category = :category')
                      ->andWhere('product.quantity > 0')
                      ->andWhere('product.outOfStock < 1')
                      ->setParameters([
                          ':category' => $category
                      ]);

        return $query->getQuery()->getResult();
    }

    /**
     * @param int $id
     * @return null|Product
     */
    public function findProductByID(int $id)
    {
        return $this->getEntityManager()
                    ->getRepository(Product::class)
                    ->findOneBy([
                        'id' => $id
                    ]);
    }

    /**
     * @return Product[]
     */
    public function getAllActiveProducts(): array
    {
        $query = $this->getEntityManager()
                      ->getRepository(Product::class)
                      ->createQueryBuilder('p')
                      ->select('p')
                      ->where('p.quantity > 0')
                      ->andWhere('p.outOfStock = 0');

        return $query->getQuery()->getResult();
    }

    /**
     * @param Product $product
     * @param User $user
     * @return bool
     */
    public function updateProduct(Product $product, User $user): bool
    {
        $em = $this->getEntityManager();
        $product->setUpdatedBy($user);
        $em->getUnitOfWork()->scheduleForUpdate($product);

        if (true === $em->getUnitOfWork()->isScheduledForUpdate($product)) {
            $em->flush();
            return true;
        }

        return true;
    }

    /**
     * @param int $categoryID
     * @return array
     * @internal param string $categoryName
     */
    public function getProductsByCategoryOnArray(int $categoryID): array
    {
        $query = $this->getEntityManager()
                      ->getRepository(Product::class)
                      ->createQueryBuilder('p')
                      ->where('p.category = :categoryID')
                      ->andWhere('p.outOfStock < 1')
                      ->andWhere('p.quantity > 0')
                      ->setParameter(':categoryID', $categoryID);

        return $query->getQuery()->getArrayResult();
    }

    /**
     * @param Promotion $promotion
     * @return array
     */
    public function getProductsByPromotion(Promotion $promotion): array
    {
        $query = $this->getEntityManager()
                      ->getRepository(Product::class)
                      ->createQueryBuilder('product')
                      ->join('product.promotion', 'promotion')
                      ->where('promotion = :promotion')
                      ->setParameter(':promotion', $promotion);

        return $query->getQuery()->getArrayResult();
    }

    /**
     * @param int $productID
     * @return array
     */
    public function getProductByIDOnArray(int $productID): array
    {
        $query = $this->getEntityManager()
                      ->getRepository(Product::class)
                      ->createQueryBuilder('product')
                      ->where('product.id = :productID')
                      ->setParameters([
                          ':productID' => $productID
                      ]);

        return $query->getQuery()->getArrayResult();
    }

    /**
     * @param string $title
     * @return null|Product
     */
    public function getProductByTitle(string $title)
    {
        return $this->getEntityManager()
                    ->getRepository(Product::class)
                    ->createQueryBuilder('product')
                    ->where('product.title = :title')
                    ->setParameters([
                        ':title' => $title
                    ])->getQuery()
                      ->getOneOrNullResult();
    }

    /**
     * @param OrderedProducts $orderedProducts
     * @param Product $product
     * @param User $user
     * @return bool
     */
    public function markAsOutOfStock(OrderedProducts $orderedProducts,
                                     Product $product,
                                     User $user): bool
    {
        $em = $this->getEntityManager();

        $product->setOutOfStock(true);
        $em->remove($orderedProducts);

        $user->setMoney($user->getMoney() - $orderedProducts->getOrderedProductPrice());

        $em->flush();
        return true;
    }

    /**
     * @param OrderedProducts $orderedProducts
     * @param Product $product
     * @param User $user
     * @param int $quantity
     * @return bool
     */
    public function decreaseQuantityOnProduct(OrderedProducts $orderedProducts,
                                              Product $product,
                                              User $user,
                                              int $quantity): bool
    {
        $em = $this->getEntityManager();

        $product->setQuantity($product->getQuantity() - $quantity);

        $orderedProducts->setQuantity($orderedProducts->getQuantity() - $quantity);
        $orderedProducts->setOrderedProductPrice($orderedProducts->getOrderedProductPrice() - $product->getPrice());

        $user->setMoney($user->getMoney() - $orderedProducts->getOrderedProductPrice());

        $em->flush();
        return true;
    }

    /**
     * @param Promotion $promotion
     * @return array
     */
    public function getProductsByPromotionOnObjects(Promotion $promotion): array
    {
        $query = $this->getEntityManager()
                      ->getRepository(Product::class)
                      ->createQueryBuilder('product')
                      ->join('product.promotion', 'promotion')
                      ->where('promotion = :promotion')
                      ->setParameters([
                        ':promotion' => $promotion
                     ]);

        return $query->getQuery()->getResult();
    }

    /**
     * @param Promotion $promotion
     * @return array
     */
    public function getNonExistingProductsInPromotion(Promotion $promotion): array
    {
        $query = $this->getEntityManager()
                      ->getRepository(Product::class)
                    ->createQueryBuilder('product')
                    ->join('product.promotion', 'promotion')
                    ->where('promotion <> :promotion')
                    ->setParameters([
                        ':promotion' => $promotion,
                    ]);

        return $query->getQuery()->getResult();
    }

    /**
     * @param Product $product
     * @param Promotion $promotion
     * @return bool
     */
    public function removeProductFromPromotion(Product $product, Promotion $promotion): bool
    {
        $em = $this->getEntityManager();
        $product->removePromotionFromProduct($promotion);
        $em->flush();
        return true;
    }
}
