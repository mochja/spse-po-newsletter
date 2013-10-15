<?php


class FormRenderer extends \Nette\Forms\Rendering\DefaultFormRenderer
{
	protected function init()
	{
		$this->wrappers['controls']['container'] = 'dl';
		$this->wrappers['pair']['container'] = NULL;
		$this->wrappers['label']['container'] = 'dt';
		$this->wrappers['control']['container'] = 'dd';
		parent::init();
	}	
}