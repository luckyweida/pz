<?php
namespace Pz\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class AssetPicker extends AbstractType {

	public function getBlockPrefix() {
		return 'assetpicker';
	}

	public function setDefaultOptions(OptionsResolverInterface $resolver) {
		$resolver->setDefaults(array(
			'compound' => false
		));
	}
}
