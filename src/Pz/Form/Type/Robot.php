<?php
namespace Pz\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class Robot extends AbstractType
{

    public function getBlockPrefix()
    {
        return 'antispam';
    }
    
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'compound' => false,
        ));
    }
}