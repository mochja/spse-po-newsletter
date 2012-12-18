<?php

/**
 * Base class for all application presenters.
 *
 * @author     John Doe
 * @package    MyApplication
 */
abstract class BasePresenter extends Nette\Application\UI\Presenter
{

	public function beforeRender()
	{
		parent::beforeRender();
		$this->template->registerHelper('nformat', function ($d) {
		    return \spse\newsletter\model\Newsletter::build_number( (int)$d );
		});
	}

}
