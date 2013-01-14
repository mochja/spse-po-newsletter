<?php

namespace AdminModule;

/**
 * Admin area, just for authentificated
 *
 * @author Jan Mochnak <janmochnak@gmail.com>
 * @package newsletter
 */

use \Nette\Forms\Form;
use \spse\newsletter\model\Newsletter;

/**
 * @User
 */
class NewsletterPresenter extends \BasePresenter
{

	/** @var Newsletter */
	private $newsletter;

	/**
	 * @var \Nette\Database\Connection
	 */
	private $database;


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
			'number' => Newsletter::build_number(Newsletter::date_to_number(new \DateTime))
		));
		$form['state']->setDisabled();
	}

	public function actionEdit($id)
	{
		$newsletter = $this->template->newsletter = $this->newsletter->get($id);

		$form = $this['newsletterForm'];
		$form->setDefaults(array(
			'number' => Newsletter::build_number($newsletter->number),
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
	}

	protected function createComponentNewsletterForm()
	{
		$form = new \Nette\Application\UI\Form;
		$form->addText('number', 'Vydanie', 10, Newsletter::date_to_number(new \DateTime))
			->addRule(Form::MIN_LENGTH, 'Vydanie musí obsahovať aspoň %d znaky.', 4)
			->addRule(Form::PATTERN, 'Nesprávny formát vydania, zadávajte vo formáte mesiac/rok. napr.: 5/13, 5/2013', '([0-9]{1,2}/[0-9]{2,4})');
		//$form->addTextarea('text');
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
		$form->addRadioList('type', 'Typ', array('článok', 'flash'))->getSeparatorPrototype()->setName(NULL);
		$form->addTextarea('text', 'Obsah');
		$form->addSubmit('submit', 'Uložiť');

		$form->onSuccess[] = callback($this, 'onNewsletterContentFormSuccess');
		return $form;
	}

	public function onNewsletterContentFormSuccess($form)
	{
		$values = $form->values;
		if ($this->getAction() == 'newContent') {
			$values['newsletter_id'] = $this->getParameter('id');
			try {
				if ($this->newsletter->add_article($values)) {
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
				if ($this->newsletter->edit_article($id, $values) !== FALSE) {
					$this->redirect( 'edit', $bnl );
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
			$nl = $this->newsletter->get_articles($id)->fetch()->newsletter_id;
			if (!$this->newsletter->del_article($id)) {
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
		$article = $this->newsletter->get_articles($id)->fetch();
		$form = $this['newsletterContentForm'];
		$form->addHidden('newsletter_id', $article->newsletter_id);
		$form->setDefaults(array(
			'title' => $article->title,
			'type' => $article->type,
			'text' => $article->text
		));
	}

}