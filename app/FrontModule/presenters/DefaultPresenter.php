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
			$this->redirect('show', $last->format('Y'), $last->format('m'));
		} else {
			throw new \Nette\Application\BadRequestException;
		}
	}

	public function actionShow($year, $month)
	{
		$state = $this->getUser()->isLoggedIn() ? array(0, 1) : 1;

		if ($month < 10) {
			$redirMonth = '0'.(int)$month;
			if ($month !== $redirMonth) {
				$this->redirect('this', $year, $redirMonth);
			}
		}

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

		$types = $this->newsletter->getArticleTypes();
		$article_map = array_values($types);
		$article_map = array_flip($article_map);

		foreach ($articles as $article) {
			if (!isset($article_map[$types[$article->type]]) || !is_array($article_map[$types[$article->type]])) {
				$article_map[$types[$article->type]] = array();
			}
			$article_map[$types[$article->type]][$article->id] = $article;
		}

		$this->template->articles = $article_map;
	}

	public function actionList()
	{
		$newsletters = array();
		$from = strtotime('2013-09-01');
		foreach ($this->newsletter->table as $newsletter) {
			$date = Newsletter::buildDatetime( $newsletter->number );
			if ( $date->getTimestamp() < $from) continue;
			$year = Newsletter::getSchoolYear( $date );
			if (!isset($newsletters[$year]) || !is_array($newsletters[$year]) ) {
				$newsletters[$year] = array();
			}
			$newsletters[$year][] = array($date, Newsletter::getMonthName($date->format('n') - 1));
		}
		$this->template->newsletters = $newsletters;
	}

	public function injectDatabase(\Nette\Database\Connection $database)
	{
		$this->database = $database;
	}

	public function injectNewsletter(Newsletter $newsletter)
	{
		$this->newsletter = $newsletter;
	}

}
