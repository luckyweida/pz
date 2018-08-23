<?php
namespace Pz\Form\Types;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class DatePicker extends AbstractType {

	public function getName() {
		return 'datepicker';
	}

	public function setDefaultOptions(OptionsResolverInterface $resolver) {
		$resolver->setDefaults(array(
			'compound' => false
		));
	}
}
