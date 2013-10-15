<?php


class Form extends \Nette\Application\UI\Form
{
	public function __construct($name = NULL)
	{
		$this->setRenderer(new FormRenderer);
		parent::__construct(NULL, $name);
	}
}