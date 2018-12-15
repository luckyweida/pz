<?php

namespace Pz\Form\Builder;

use Pz\Form\Constraints\ConstraintRequired;
use Pz\Orm\Country;
use Pz\Orm\DeliveryOption;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
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

        $connection = $options['container']->get('doctrine.dbal.default_connection');
        $pdo = $connection->getWrappedConnection();

        $countries = array();

        /** @var Country[] $result */
        $result = array();
        /** @var DeliveryOption[] $result */
        $deliveryOptions = DeliveryOption::active($pdo);
        foreach ($deliveryOptions as $itm) {
            $result = array_merge($result, $itm->objCountries());
        }
        foreach ($result as $itm) {
            $countries[$itm->getTitle()] = $itm->getCode();
        }

        ksort($countries);

//        var_dump($countries);exit;

        parent::buildForm($builder, $options);

        $builder->add('action', TextType::class, array(
            'mapped' => false,
        ))->add('email', TextType::class, array(
            'label' => 'Email address:',
            'constraints' => array(
                new Assert\NotBlank(),
                new Assert\Email(),
            )
        ))->add('billingFirstName', TextType::class, array(
            'label' => 'First name:',
            'constraints' => array(
                new Assert\NotBlank(),
            )
        ))->add('billingLastName', TextType::class, array(
            'label' => 'Last name:',
            'constraints' => array(
                new Assert\NotBlank(),
            )
        ))->add('billingPhone', TextType::class, array(
            'label' => 'Phone:',
            'constraints' => array(
                new Assert\NotBlank(),
            )
        ))->add('billingAddress', TextType::class, array(
            'label' => 'Address:',
            'constraints' => array(
                new Assert\NotBlank(),
            )
        ))->add('billingAddress2', TextType::class, array(
            'label' => 'Address2:',
        ))->add('billingCity', TextType::class, array(
            'label' => 'City:',
            'constraints' => array(
                new Assert\NotBlank(),
            )
        ))->add('billingPostcode', TextType::class, array(
            'label' => 'Postcode:',
            'constraints' => array(
                new Assert\NotBlank(),
            )
        ))->add('billingState', TextType::class, array(
            'label' => 'State:',
        ))->add('billingCountry', ChoiceType::class, array(
            'required'   => false,
            'empty_data' => 'Choose a country',
            'label' => 'Country:',
            'choices' => $countries,
            'constraints' => array(
                new Assert\NotBlank(),
            )
        ))->add('billingSave', CheckboxType::class, array(
            'label' => 'Save this address',
        ))->add('note', TextareaType::class, array(
            'label' => 'Note:',
        ))->add('billingSame', CheckboxType::class, array(
            'label' => 'Same as Billing Address',
        ))->add('shippingFirstName', TextType::class, array(
            'label' => 'First name:',
            'constraints' => array(
                new ConstraintRequired(array(
                    'form' => $this,
                    'fieldToCheck' => 'billingSame',
                )),
            )
        ))->add('shippingLastName', TextType::class, array(
            'label' => 'Last Name:',
            'constraints' => array(
                new ConstraintRequired(array(
                    'form' => $this,
                    'fieldToCheck' => 'billingSame',
                )),
            )
        ))->add('shippingPhone', TextType::class, array(
            'label' => 'Phone:',
            'constraints' => array(
                new ConstraintRequired(array(
                    'form' => $this,
                    'fieldToCheck' => 'billingSame',
                )),
            )
        ))->add('shippingAddress', TextType::class, array(
            'label' => 'Address:',
            'constraints' => array(
                new ConstraintRequired(array(
                    'form' => $this,
                    'fieldToCheck' => 'billingSame',
                )),
            )
        ))->add('shippingAddress2', TextType::class, array(
            'label' => 'Address2:',
        ))->add('shippingCity', TextType::class, array(
            'label' => 'City:',
            'constraints' => array(
                new ConstraintRequired(array(
                    'form' => $this,
                    'fieldToCheck' => 'billingSame',
                )),
            )
        ))->add('shippingPostcode', TextType::class, array(
            'label' => 'Postcode:',
            'constraints' => array(
                new ConstraintRequired(array(
                    'form' => $this,
                    'fieldToCheck' => 'billingSame',
                )),
            )
        ))->add('shippingState', TextType::class, array(
            'label' => 'State:',
        ))->add('shippingCountry', ChoiceType::class, array(
            'required'   => false,
            'empty_data' => 'Choose a country',
            'label' => 'Country:',
            'choices' => $countries,
            'constraints' => array(
                new ConstraintRequired(array(
                    'form' => $this,
                    'fieldToCheck' => 'billingSame',
                )),
            )
        ))->add('shippingSave', CheckboxType::class, array(
            'label' => 'Save this address',
        ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setDefaults(array(
            'container' => null,
        ));
    }
}
