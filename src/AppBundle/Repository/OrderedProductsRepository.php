<?php

namespace AppBundle\Repository;

use AppBundle\Entity\OrderedProducts;
use AppBundle\Entity\Product;
use AppBundle\Entity\User;
use Doctrine\ORM\EntityRepository;

/**
 * OrderedProductsRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class OrderedProductsRepository extends EntityRepository implements IOrderedProductsRepository
{
    /**
     * @param User $user
     * @return array
     */
    public function getOrderedProductsByUser(User $user): array
    {
        $query = $this->getEntityManager()
                      ->getRepository(OrderedProducts::class)
                      ->createQueryBuilder('op')
                      ->select('op.id as orderedProductID, pr.title AS Product, op.orderedDate, op.orderedProductPrice AS Price, 
                                      op.quantity AS Quantity, op.confirmed AS Confirmed, u.username AS User')
                      ->join('op.user','u')
                      ->join('op.product', 'pr')
                      ->where('u = :user')
                      ->setParameter(':user', $user)
                      ->getQuery();

        return $query->getArrayResult();
    }

    /**
     * @param User $user
     * @param Product $product
     * @return bool
     * @throws \Exception
     */
    public function addOrderedProduct(User $user, Product $product): bool
    {
        $em = $this->getEntityManager();

        if ($product->getQuantity() > 1) {
            $product->setQuantity($product->getQuantity() - 1);
        } else {
            $product->setQuantity(0);
            $product->setOutOfStock(true);
        }

        $orderedProduct = new OrderedProducts();
        $orderedProduct->setUser($user);
        $orderedProduct->setOrderedDate(new \DateTime('now'));
        $orderedProduct->setConfirmed(false);
        $orderedProduct->setQuantity(1);
        $orderedProduct->setProduct($product);
        $orderedProduct->setOrderedProductPrice($product->getPrice());

        $em->persist($orderedProduct);
        $em->persist($product);

        $em->getUnitOfWork()->scheduleForUpdate($product);
        $em->getUnitOfWork()->scheduleForUpdate($user);

        if (true === $em->getUnitOfWork()->isEntityScheduled($orderedProduct)
            && true === $em->getUnitOfWork()->isScheduledForUpdate($user)
            && true === $em->getUnitOfWork()->isScheduledForUpdate($product)) {
                $userMoney = $user->getMoney() - $product->getPrice();
                $user->setMoney($userMoney);
                $em->persist($user);
                $em->flush();
                return true;
        }
        return false;
    }

    /**
     * @param Product $product
     * @return OrderedProducts|null|object
     */
    public function findOrderedProductByProduct(Product $product)
    {
        return  $this->getEntityManager()
                     ->getRepository(OrderedProducts::class)
                     ->findOneBy([
                         'product' => $product
                     ]);
    }

    /**
     * @param OrderedProducts $product
     * @return bool
     */
    public function removeOrderedProduct(OrderedProducts $product): bool
    {
        $em = $this->getEntityManager();
        $em->remove($product);

        if (true === $em->getUnitOfWork()->isScheduledForDelete($product)) {
            $em->flush();
            return true;
        }

        return false;
    }

    /**
     * @param OrderedProducts $orderedProduct
     * @param Product $product
     * @return bool
     */
    public function increaseQuantity(OrderedProducts $orderedProduct, Product $product): bool
    {
        $em = $this->getEntityManager();
        $em->getUnitOfWork()->scheduleForUpdate($orderedProduct);
        $user = $orderedProduct->getUser();
        $user->setMoney($user->getMoney() - $product->getPrice());

        $quantity = $orderedProduct->getQuantity() + 1;
        $increasePrice = $orderedProduct->getOrderedProductPrice() + $product->getPrice();

        $product->setQuantity($product->getQuantity() - 1);

        $orderedProduct->setQuantity($quantity);
        $orderedProduct->setOrderedProductPrice($increasePrice);
        $orderedProduct->setOrderedDate(new \DateTime('now'));

        $em->getUnitOfWork()->scheduleForUpdate($product);
        $em->getUnitOfWork()->scheduleForUpdate($orderedProduct);

        if (true === $em->getUnitOfWork()->isScheduledForUpdate($orderedProduct) &&
            true === $em->getUnitOfWork()->isScheduledForUpdate($product)) {
                $em->persist($user);
                $em->flush();
                return true;
        }

        return false;
    }

    /**
     * @param User $user
     * @param Product $product
     * @return OrderedProducts|null|object
     */
    public function findOrderedProductFromUserByID(User $user, Product $product)
    {
        return $this->getEntityManager()
                    ->getRepository(OrderedProducts::class)
                    ->findOneBy([
                        'user'      => $user,
                        'product'   => $product
                    ]);
    }

    public function findOrderedProductByID(int $id)
    {
        return $this->getEntityManager()
                     ->getRepository(OrderedProducts::class)
                     ->findOneBy([
                         'id' => $id
                     ]);
    }

    /**
     * @param OrderedProducts $orderedProduct
     * @param Product $product
     * @return bool
     */
    public function decreaseQuantity(OrderedProducts $orderedProduct, Product $product): bool
    {
        $em = $this->getEntityManager();

        $orderedProductQuantity = $orderedProduct->getQuantity();
        $productQuantity        = $product->getQuantity();

        $em->getUnitOfWork()->scheduleForUpdate($orderedProduct);
        $em->getUnitOfWork()->scheduleForUpdate($product);

        $orderedProduct->setQuantity($orderedProductQuantity - 1);
        $product->setQuantity($productQuantity + 1);

        $em->persist($orderedProduct);
        $em->persist($product);

        if (true === $em->getUnitOfWork()->isScheduledForUpdate($orderedProduct) &&
            true === $em->getUnitOfWork()->isScheduledForUpdate($product))
        {
            $em->flush();
            return true;
        }

        return false;
    }
}
