<?php
namespace Pz\Form\Builder;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints as Assert;

class Model extends AbstractType
{

    public function getName()
    {
        return 'form';
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $defaultSortByOptions = isset($options['defaultSortByOptions']) ? $options['defaultSortByOptions'] : array();

        $builder->add('title', TextType::class, array(
            'label' => 'Name:',
            'constraints' => array(
                new Assert\NotBlank()
            )
        ))->add('className', TextType::class, array(
            'label' => 'Class name:',
            'constraints' => array(
                new Assert\NotBlank()
            )
        ))->add('modelType', ChoiceType::class, array(
            'label' => 'Model type:',
            'expanded' => true,
            'choices' => array(
                'Customised' => 0,
                'Built in' => 1,
            )
        ))->add('dataType', ChoiceType::class, array(
            'label' => 'Data type:',
            'expanded' => true,
            'choices' => array(
                'Database' => 0,
                'Admin' => 1,
                'None' => 2,
            )
        ))->add('listType', ChoiceType::class, array(
            'label' => 'Listing type:',
            'expanded' => true,
            'choices' => array(
                'Drag & Drop' => 0,
                'Pagination' => 1,
                'Tree' => 2,
            )
        ))->add('numberPerPage', TextType::class, array(
            'label' => 'Page size:',
        ))->add('defaultSortBy', ChoiceType::class, array(
            'label' => 'Sort:',
            'choices' => $defaultSortByOptions,
        ))->add('defaultOrder', ChoiceType::class, array(
            'label' => 'Order:',
            'expanded' => true,
            'choices' => array(
                'ASC' => 0,
                'DESC' => 1,
            )
        ))->add('columnsJson', HiddenType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setDefaults(array(
            'defaultSortByOptions' => array(),
        ));
    }
}
