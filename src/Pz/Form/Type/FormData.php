<?php
namespace Pz\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class FormData extends AbstractType {

	public function getBlockPrefix() {
		return 'formdata';
	}

	public function setDefaultOptions(OptionsResolverInterface $resolver) {
		$resolver->setDefaults(array(
			'compound' => false
		));
	}
}
