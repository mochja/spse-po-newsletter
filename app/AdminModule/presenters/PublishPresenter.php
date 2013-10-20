<?php

namespace AdminModule;

use \spse\newsletter\model\Newsletter;
use \Nette\Database\Connection;
use Nette\Application\UI\Form;

/**
 *
 * @author Jan Mochnak <janmochnak@gmail.com>
 * @copyright 2013
 */

class PublishPresenter extends \BasePresenter
{

	/**
	 * @var \Nette\Database\Connection
	 */
	private $database;

	/** @var Newsletter */
	private $newsletter;


	public function actionDefault()
	{
	}

	public function actionNewsletter($id)
	{
		$template = $this->newsletter->generateTemplate($id);
		$this->template->emailContent = (string) $template;
	}

	public function actionGenerateEmailTemplate($id)
	{
		$template = $this->newsletter->generateTemplate($id);
		$this->template->emailContent = (string) $template;
	}

	protected function createComponentPublishForm()
	{
		$form = new Form;

		$numbers = array();

		foreach ($this->newsletter->table->order('id DESC') as $row) {
			$numbers[$row->id] = Newsletter::buildNumber($row->number);
		}

		$form->addSelect('newsletter_id', 'Publikácia newslettera: ', $numbers);
		$form->addSubmit('submit', 'Publikovať');

		$form->onSuccess[] = $this->onPublishFormSuccess;

		return $form;
	}

	public function onPublishFormSuccess(Form $form)
	{
		$values = $form->getValues();
		$this->redirect('newsletter', $values['newsletter_id']);
	}

	public function injectNewsletter(Newsletter $newsletter)
	{
		$this->newsletter = $newsletter;
	}

	public function injectDatabase(Connection $database)
	{
		$this->database = $database;
	}

}