<?php

namespace FrontModule;

/**
 * Default presenter.
 *
 * @author Jan Mochnak <janmochnak@gmail.com>
 * @package newsletter
 */
class DefaultPresenter extends \BasePresenter
{

	public function startup()
	{
		parent::startup();
	}

	public function actionDefault()
	{
		$db = $this->context->database;

		$state = $this->getUser()->isLoggedIn() ? 0 : 1;

		$last = $db->table('newsletter')->where('state', $state)->order('id DESC')->limit(1)->fetch();
		if ($last) {
			$last = \spse\newsletter\model\Newsletter::build_datetime($last->number);
			$this->redirect('show', $last->format("n"), $last->format("Y"));
		}
	}

	public function actionShow($year, $month)
	{

		$db = $this->context->database;

		$state = $this->getUser()->isLoggedIn() ? 1 : 1;

		$newsletter = $this->template->newsletter = $db->table('newsletter')->where('state', $state)->where('number', \spse\newsletter\model\Newsletter::date_to_number(new \DateTime($year."-".$month."-01")))->limit(1)->fetch();

		if (!$newsletter) {
			throw new \Nette\Application\BadRequestException('Newsletter does not exist or denied permission.');
		}

		$articles = $db->table('newsletter_article')->select("*")->where('newsletter_id', $newsletter->id)->order('pos')->fetchPairs("id");
		$fibers = array();

		foreach( $articles as $article ) {
			if ($article && $article->type == 1) { // a flash to another array
				$fibers[$article->id] = clone $article;
				unset($articles[$article->id]);
			}
		}

		$this->template->articles = $articles;
		$this->template->fibers = $fibers;

		/*
		$finder = \Nette\Utils\Finder::findFiles("*")->from(WWW_DIR.'/uploads');
		foreach($finder as $file) {
			$img = Nette\Image::fromFile($file->getRealPath());
			echo (string) Nette\Utils\Html ::el("img", array("src"=> '/uploads/sponsors/'.$file->getFilename(), "width" => $img->getWidth(), "height" => $img->getHeight() )); // $file->getFilename()
			echo "\n";
		}
		*/

	}

}
