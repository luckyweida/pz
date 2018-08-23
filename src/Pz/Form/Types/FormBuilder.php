<?php
namespace Pz\Form\Types;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class FormBuilder extends AbstractType {

	public function getName() {
		return 'formbuilder';
	}

	public function setDefaultOptions(OptionsResolverInterface $resolver) {
		$resolver->setDefaults(array(
			'compound' => false
		));
	}
}
