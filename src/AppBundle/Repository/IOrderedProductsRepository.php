<?php

namespace AppBundle\Repository;

use AppBundle\Entity\OrderedProducts;
use AppBundle\Entity\Product;
use AppBundle\Entity\User;

interface IOrderedProductsRepository
{
    /**
     * @param User $user
     * @return OrderedProducts[]
     */
    public function getOrderedProductsByUser(User $user): array;

    /**
     * @param User $user
     * @param Product $product
     * @return bool
     */
    public function addOrderedProduct(User $user, Product $product): bool;

    /**
     * @param Product $product
     * @return mixed
     */
    public function findOrderedProductByProduct(Product $product);

    public function findOrderedProductByID(int $id);

    /**
     * @param User $user
     * @param Product $product
     * @return mixed
     * @internal param int $id
     */
    public function findOrderedProductFromUserByID(User $user, Product $product);

    /**
     * @param OrderedProducts $orderedProducts
     * @param User $user
     * @param Product $product
     * @return bool
     */
    public function removeOrderedProduct(OrderedProducts $orderedProducts,
                                         User $user,
                                         Product $product): bool;

    /**
     * @param OrderedProducts $orderedProduct
     * @param Product $product
     * @return bool
     */
    public function increaseQuantity(OrderedProducts $orderedProduct, Product $product): bool;

    /**
     * @param OrderedProducts $orderedProduct
     * @param Product $product
     * @param User $user
     * @return bool
     */
    public function decreaseQuantity(OrderedProducts $orderedProduct,
                                     Product $product,
                                     User $user): bool;

    /**
     * @return float
     */
    public function getCheckoutFromAllProducts(): float;
}