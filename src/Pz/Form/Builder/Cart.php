<?php

namespace Pz\Form\Builder;

use Pz\Form\Constraints\ConstraintBillingRequired;
use Pz\Form\Constraints\ConstraintIfNotSameThenRequired;
use Pz\Form\Type\ChoiceMultiJson;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints as Assert;

class Cart extends AbstractType
{

    public function getBlockPrefix()
    {
        return 'cart';
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->add('action', TextType::class, array(
            'mapped' => false,
            'constraints' => array(
//                new Assert\NotBlank(),
            )
        ))->add('email', TextType::class, array(
            'label' => 'Email address:',
            'constraints' => array(
                new Assert\NotBlank(),
                new Assert\Email(),
            )
        ))->add('shippingFirstName', TextType::class, array(
            'label' => 'First name:',
            'constraints' => array(
                new Assert\NotBlank(),
            )
        ))->add('shippingLastName', TextType::class, array(
            'label' => 'Last Name:',
            'constraints' => array(
                new Assert\NotBlank(),
            )
        ))->add('shippingAddress', TextType::class, array(
            'label' => 'Address:',
            'constraints' => array(
                new Assert\NotBlank(),
            )
        ))->add('shippingAddress2', TextType::class, array(
            'label' => 'Address2:',
            'constraints' => array(//                new Assert\NotBlank(),
            )
        ))->add('shippingSuburb', TextType::class, array(
            'label' => 'Suburb:',
            'constraints' => array(
                new Assert\NotBlank(),
            )
        ))->add('shippingCity', TextType::class, array(
            'label' => 'City:',
            'constraints' => array(
                new Assert\NotBlank(),
            )
        ))->add('shippingPostcode', TextType::class, array(
            'label' => 'Postcode:',
            'constraints' => array(
                new Assert\NotBlank(),
            )
        ))->add('shippingCountry', TextType::class, array(
            'label' => 'Country:',
            'constraints' => array(
                new Assert\NotBlank(),
            )
        ))->add('shippingSave', CheckboxType::class, array(
            'label' => 'Save this address',
            'constraints' => array(//                new Assert\NotBlank(),
            )
        ))->add('note', TextareaType::class, array(
            'label' => 'Note:',
            'constraints' => array(//                new Assert\NotBlank(),
            )
        ))->add('billingSame', CheckboxType::class, array(
            'label' => 'Same as Shipping Address',
            'constraints' => array(//                new Assert\NotBlank(),
            )
        ))->add('billingFirstName', TextType::class, array(
            'label' => 'First name:',
            'constraints' => array(
                new ConstraintIfNotSameThenRequired(array(
                    'form' => $this,
                    'attrToCheckIfSame' => 'billingSame',
                )),
            )
        ))->add('billingLastName', TextType::class, array(
            'label' => 'Last name:',
            'constraints' => array(
                new ConstraintIfNotSameThenRequired(array(
                    'form' => $this,
                    'attrToCheckIfSame' => 'billingSame',
                )),
            )
        ))->add('billingAddress', TextType::class, array(
            'label' => 'Address:',
            'constraints' => array(
                new ConstraintIfNotSameThenRequired(array(
                    'form' => $this,
                    'attrToCheckIfSame' => 'billingSame',
                )),
            )
        ))->add('billingAddress2', TextType::class, array(
            'label' => 'Address2:',
            'constraints' => array(
//                new ConstraintIfNotSameThenRequired(array(
//                    'form' => $this,
//                    'attrToCheckIfSame' => 'billingSame',
//                )),
            )
        ))->add('billingSuburb', TextType::class, array(
            'label' => 'Suburb:',
            'constraints' => array(
                new ConstraintIfNotSameThenRequired(array(
                    'form' => $this,
                    'attrToCheckIfSame' => 'billingSame',
                )),
            )
        ))->add('billingCity', TextType::class, array(
            'label' => 'City:',
            'constraints' => array(
                new ConstraintIfNotSameThenRequired(array(
                    'form' => $this,
                    'attrToCheckIfSame' => 'billingSame',
                )),
            )
        ))->add('billingPostcode', TextType::class, array(
            'label' => 'Postcode:',
            'constraints' => array(
                new ConstraintIfNotSameThenRequired(array(
                    'form' => $this,
                    'attrToCheckIfSame' => 'billingSame',
                )),
            )
        ))->add('billingCountry', TextType::class, array(
            'label' => 'Country:',
            'constraints' => array(
                new ConstraintIfNotSameThenRequired(array(
                    'form' => $this,
                    'attrToCheckIfSame' => 'billingSame',
                )),
            )
        ))->add('billingSave', CheckboxType::class, array(
            'label' => 'Save this address',
            'constraints' => array(
//                new ConstraintIfNotSameThenRequired(array(
//                    'form' => $this,
//                    'attrToCheckIfSame' => 'billingSame',
//                )),
            )
        ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setDefaults(array());
    }
}
