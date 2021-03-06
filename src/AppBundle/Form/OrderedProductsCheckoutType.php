<?php

namespace AppBundle\Form;

use AppBundle\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrderedProductsCheckoutType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $orderedProducts = $options['orderedProducts'];
        dump($orderedProducts);
        $builder->add('email', EmailType::class, [
            'label' => 'Verify Email',
        ])->add('password', PasswordType::class, [
            'label' => 'Verify Password'
        ])->add('Checkout', SubmitType::class, [
            'attr' => ['class' => 'btn btn-success']
        ])->add('products', ChoiceType::class, [
            'label' => 'Products',
            'choices' => $orderedProducts,
        ]);
    }

    public function getName()
    {
        return 'app_ordered_products_checkout';
    }

    public function getBlockPrefix()
    {
        return $this->getName();
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class'         => User::class,
            'translation_domain' => false,
            'orderedProducts'    => []
        ]);
    }
}