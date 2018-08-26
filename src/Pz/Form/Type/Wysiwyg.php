<?php
namespace Pz\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class Wysiwyg extends AbstractType {

	public function getBlockPrefix() {
		return 'wysiwyg';
	}

	public function setDefaultOptions(OptionsResolverInterface $resolver) {
		$resolver->setDefaults(array(
			'compound' => false
		));
	}
}
