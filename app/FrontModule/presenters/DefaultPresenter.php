<?php

namespace FrontModule;

use \spse\newsletter\model\Newsletter;

/**
 * Default presenter.
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

	/** @var Newsletter */
	private $newsletter;

	public function injectDatabase(\Nette\Database\Connection $database)
	{
		$this->database = $database;
	}

	public function injectNewsletter(Newsletter $newsletter)
	{
		$this->newsletter = $newsletter;
	}

	public function actionDefault()
	{
		$state = $this->getUser()->isLoggedIn() ? array(0, 1) : 1;

		$last = $this->database->table('newsletter')
			->select('number')
			->where('state', $state)
			->order('id DESC')
			->limit(1)
			->fetch();

		if ($last) {
			$last = $this->newsletter->buildDatetime($last->number);
			$this->redirect('show', $last->format('Y'), $last->format('n'));
		} else {
			throw new \Nette\Application\BadRequestException;
		}
	}

	public function actionShow($year, $month)
	{
		$state = $this->getUser()->isLoggedIn() ? array(0, 1) : 1;

		$date = new \DateTime($year.'-'.$month.'-01');
		$numberHash = $this->newsletter->dateToNumber($date);

		$newsletter = $this->template->newsletter = $this->database->table('newsletter')
			->where('state', $state)
			->where('number', $numberHash)
			->limit(1)
			->fetch();

		if ($newsletter === FALSE) {
			throw new \Nette\Application\BadRequestException('Newsletter does not exist or permission denied.');
		}

		$articles = $this->database->table('newsletter_article')
			->select('id, type, title, text, html, author')
			->where('newsletter_id', $newsletter->id)
			->order('pos')
			->fetchPairs('id');

		$fibers = array();

		foreach( $articles as $article ) {
			if ($article && $article->type == 1) { // a flash to another array
				$fibers[$article->id] = clone $article;
				unset($articles[$article->id]);
			}
		}

		$this->template->articles = $articles;
		$this->template->fibers = $fibers;
	}

}
