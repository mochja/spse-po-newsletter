<?php

namespace AdminModule;

use \Nette\Forms\Form;

/**
 * Admin area, just for authentificated
 *
 * @author Jan Mochnak <janmochnak@gmail.com>
 * @copyright 2013
 */
class DefaultPresenter extends \BasePresenter
{

	/**
	 * @var \Nette\Database\Connection
	 */
	private $database;

	public function injectDatabase(\Nette\Database\Connection $database)
	{
		$this->database = $database;
	}

	public function startup()
	{
		parent::startup();
		if ( $this->user->isLoggedIn() === FALSE ) {
			$this->redirect('Sign:in');
		}
	}

	public function actionDefault()
	{
		$newsletters = $this->template->newsletters = $this->database->table('newsletter')
			->select('id, number')
			->order('created DESC, published DESC, id DESC, number DESC');

		$this->template->emailCount = $this->database->table('newsletter_email')
			->count('email');
	}

}