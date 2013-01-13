<?php

namespace AdminModule;

/**
 * Admin area, just for authentificated
 *
 * @author Jan Mochnak <janmochnak@gmail.com>
 * @package newsletter
 */

use \Nette\Forms\Form;

class DefaultPresenter extends \BasePresenter
{

	public function startup()
	{
		parent::startup();
		if ( ! $this->user->isLoggedIn() ) {
			$this->redirect('Sign:in');
		}
	}

	public function actionDefault()
	{
		$db = $this->context->database;

		$newsletters = $this->template->newsletters = $db->table('newsletter')->select("id,number")->order('published DESC, id DESC, number DESC');

		$this->template->email_count = $db->table('newsletter_email')->count("email");
	}

}