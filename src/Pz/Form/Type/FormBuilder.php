<?php
namespace Pz\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class FormBuilder extends AbstractType {

	public function getBlockPrefix() {
		return 'formbuilder';
	}

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array(
            'compound' => false,
        ));
    }
}
