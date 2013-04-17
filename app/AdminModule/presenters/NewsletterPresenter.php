<?php

namespace AdminModule;

use \Nette\Forms\Form;
use \spse\newsletter\model\Newsletter;
use \Michelf\MarkdownExtra;
use \Custom\Finder;

/**
 * Admin area, just for authentificated
 *
 * @User
 * @author Jan Mochnak <janmochnak@gmail.com>
 * @copyright 2013
 */
class NewsletterPresenter extends \BasePresenter
{

	/** @var Newsletter */
	private $newsletter;

	/**
	 * @var \Nette\Database\Connection
	 */
	private $database;

	private $path = '/uploads/photos';



	public function injectNewsletter(Newsletter $newsletter)
	{
		$this->newsletter = $newsletter;
	}

	public function injectDatabase(\Nette\Database\Connection $database)
	{
		$this->database = $database;
	}

	public function actionDefault()
	{

	}

	public function actionAdd()
	{
		$form = $this['newsletterForm'];
		$form->setDefaults(array(
			'number' => Newsletter::buildNumber(Newsletter::dateToNumber(new \DateTime))
		));
		$form['state']->setDisabled();
	}

	public function actionEdit($id)
	{
		$newsletter = $this->template->newsletter = $this->newsletter->get($id);

		$form = $this['newsletterForm'];
		$form->setDefaults(array(
			'number' => Newsletter::buildNumber($newsletter->number),
			'state' => $newsletter->state
		));

		$articles = $this->database->table('newsletter_article')->
			where('newsletter_id', $newsletter->id)
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
		$this->template->number = Newsletter::buildNumber($newsletter->number);
	}

	protected function createComponentNewsletterForm()
	{
		$form = new \Nette\Application\UI\Form;
		$form->addText('number', 'Vydanie', 10, Newsletter::dateToNumber(new \DateTime))
			->addRule(Form::MIN_LENGTH, 'Vydanie musí obsahovať aspoň %d znaky.', 4)
			->addRule(Form::PATTERN, 'Nesprávny formát vydania, zadávajte vo formáte mesiac/rok. napr.: 5/13, 5/2013', '([0-9]{1,2}/[0-9]{2,4})');
		$form->addCheckbox('state', 'Publikovať');
		$form->addSubmit('s', 'Uložiť');

		$form->onSuccess[] = callback($this, 'onNewsletterFormSuccess');
		return $form;
	}

	public function onNewsletterFormSuccess($form)
	{
		$values = $form->values;
		sscanf($values['number'], '%d/%d', $month, $year);
		$year = $year > 1999 ? $year - 2000 : $year;
		$values['number'] = (int)($month.$year);
		$values['published'] = null;

		if ($this->getAction() == 'add') {
			$values['state'] = 0;
			$values['created'] = new \DateTime;
			try {
				$new = $this->newsletter->insert($values);
			} catch ( \PDOException $e) {
				$form->addError($e->getMessage());
			}
			if ( isset($new) ) {
				$this->redirect('edit', $new->id);
			}
		} else {
			try {
				$values['state'] = isset($values['state']) && $values['state'] == 1 ? $values['state'] : 0;
				$this->newsletter->update((int) $this->getParameter('id'), $values);
				$this->redirect('edit', $this->getParameter('id'));
			} catch(\PDOException $e) {
				$form->addError($e->getMessage());
			}
		}
	}

	public function actionNewContent($id, $type)
	{
		$form = $this['newsletterContentForm'];
		$form->setDefaults(array(
			'type' => isset($type) && $type == 'flash' ? 1 : 0
		));
	}

	protected function createComponentNewsletterContentForm()
	{
		$form = new \Nette\Application\UI\Form;
		$form->addText('title', 'Titulok', 60);
		$form->addText('author', 'Autor', 40);
		$form->addRadioList('type', 'Typ', array('článok', 'flash'))->getSeparatorPrototype()->setName(NULL);
		$form->addTextarea('text', 'Obsah', 120, 27);
		$form->addSubmit('submit', 'Uložiť');

		$form->onSuccess[] = callback($this, 'onNewsletterContentFormSuccess');
		return $form;
	}

	public function onNewsletterContentFormSuccess($form)
	{
		$values = $form->values;

		$values['html'] = MarkdownExtra::defaultTransform($values->text);

		if ($this->getAction() == 'newContent') {
			$values['newsletter_id'] = $this->getParameter('id');
			try {
				if ($this->newsletter->addArticle($values)) {
					$this->redirect('edit', $values['newsletter_id']);
				} else {
					$form->addError('Nepodarilo sa ulozit obsah');
				}
			} catch (\PDOException $e) {
				$form->addError($e->getMessage());
			}
		} else if ($this->getAction() == 'editContent') {
			$id = $this->getParameter('id');
			$bnl = $values['newsletter_id'];
			unset($values['newsletter_id']);
			try {
				if ($this->newsletter->editArticle($id, $values) !== FALSE) {
					$this->redirect('edit', $bnl );
				} else {
					$form->addError('Nepodarilo sa upravit obsah...');
				}
			} catch (\PDOException $e) {
				$form->addError($e->getMessage());
			}
		}
	}

	public function actionDelContent($id)
	{
		try {
			$nl = $this->newsletter->getArticles($id)->fetch()->newsletter_id;
			if (!$this->newsletter->delArticle($id)) {
				$this->flashMessage('Nepodarilo sa odstranit obsah', 'error');
			} else {
				$this->flashMessage('Obsah odstaneny', 'info');
			}
			$this->redirect('edit', $nl);
		} catch (\PDOException $e) {
			$this->flashMessage('Nepodarilo sa odstranit obsah', 'error');
			$this->flashMessage($e->getMessage(), 'warning');
			$this->redirect('default:');
		}
	}

	public function actionEditContent($id)
	{
		$article = $this->newsletter->getArticles($id)->fetch();
		$form = $this['newsletterContentForm'];
		$form->addHidden('newsletter_id', $article->newsletter_id);
		$form->setDefaults(array(
			'title' => $article->title,
			'type' => $article->type,
			'text' => $article->text,
			'author' => $article->author
		));
	}


	public function actionModal()
	{
		$basePath = preg_replace('#https?://[^/]+#A', '', rtrim($this->context->httpRequest->url->baseUrl, '/'));

		$filelist = array();
		foreach (Finder::findFiles('t_*.jpg')->in(WWW_DIR.$this->path)->orderByMTime() as $key => $file)
		{
			$title = $file->getFilename();
			if ($offset = strrpos($title, '_')) {
				$title = substr($title, $offset+1);
			}
			if ($offset = strrpos($title, '.')) {
				$title = substr($title, 0, $offset);
			}
			$filelist[] = array(
				'thumb' => $basePath.'/system'.$this->path.'/'.$file->getFilename(),
				'image' => $basePath.'/system'.$this->path.'/'.substr($file->getFilename(), 2),
				'title' => $title,
				'folder' => 'default'
			);
		}

		$this->template->filelist = $filelist;
	}

}