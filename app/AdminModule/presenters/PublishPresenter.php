<?php

namespace AdminModule;

use \spse\newsletter\model\Newsletter;
use \Nette\Database\Connection;

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

	public function actionGenerateEmailTemplate($id)
	{
		$newsletter = $this->newsletter->get((int) $id);

		$template = new \Nette\Templating\FileTemplate(__DIR__.'/../templates/email.latte');
		$template->registerFilter(new \Nette\Latte\Engine);
		$template->registerHelperLoader('Nette\Templating\Helpers::loader');

		$datetime = $this->newsletter->buildDatetime( $newsletter->number );
		$number = $template->number = $datetime->format('Y'). '-' .$datetime->format('n');

		$template->articles = $this->database->table('newsletter_article')
			->where('type', 0)
			->where('newsletter_id', (int) $id)
			->order('pos, id');

		$template->classes = $this->database->table('newsletter_article')
			->where('type', 2)
			->where('newsletter_id', (int) $id)
			->order('pos, id')
			->select('title');

		$template->tops = $this->database->table('newsletter_article')
			->where('type', 2)
			->where('newsletter_id', (int) $id)
			->order('pos, id')
			->select('title');

		$this->template->emailContent = (string) $template;

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