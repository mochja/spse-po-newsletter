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

	/** @var \Mailchimp */
	private $mc;

	public function actionDefault()
	{
		$this->template->campaigns = $this->mc->campaigns->getList();
	}

	public function actionNewsletter($id)
	{
		$template = $this->newsletter->generateTemplate($id, __DIR__.'/../templates/mailchimp.latte');

		$newsletter = $this->newsletter->get((int) $id);
		$date = Newsletter::buildDatetime($newsletter->number);
		$number = Newsletter::buildNumber($newsletter->number);

		$createCampaign = array(
			'options' => array(
				'list_id' => '35de46fc1c',
				'subject' => 'Newsletter Impulz - '.Newsletter::getMonthName($date->format("m")-1).' '.$date->format("Y"),
				'from_email' => 'impulz@spse-po.sk',
				'from_name' => 'Newsletter Impulz',
				'template_id' => 48405,
				'generate_text' => true
			),
			'content' => array('sections' => array(
				'std_content00' => (string) $template,
				'header_image' => '<img src="http://www.spse-po.sk/newsletter/resources/mailheader_'.$number.'.png" style="max-width:600px;" id="headerImage campaign-icon" />'
			))
		);
		$campaign = $this->mc->campaigns->create('regular', $createCampaign['options'], $createCampaign['content']);
		$this->template->campaign = $campaign;
		$this->redirect('campaign', $campaign['id']);
	}

	public function actionCampaign($id)
	{
		$campaign = $this->mc->campaigns->getList( array('campaign_id' => $id ) );
		if (isset($campaign['errors']) && !empty($campaign['errors'])) throw new \Exception($campaign['errors'][0]['error']);
		$this->template->campaign = $campaign['data'][0];
	}

	public function handleSendTest($cid)
	{
		$this->mc->campaigns->sendTest($cid, array('janmochnak@icloud.com'), 'html');
	}

	public function actionSchedule($cid)
	{
		$date = new \DateTime('tomorrow');
		try {
			$schedule = $this->mc->campaigns->schedule($cid, $date->format('Y-m-d 00:00:00') );
		} catch (\Exception $e ) {
			$this->flashMessage('Campaign could not be scheduled', 'error');
		}

		if (isset($schedule['complete']) && $schedule['complete'] === TRUE) {
			$this->flashMessage('Campaign was successfuly scheduled.');
		}
		$this->redirect('default');
	}

	public function actionRemoveCampaign($cid)
	{
		$this->mc->campaigns->delete($cid);
		$this->redirect('default');
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

	public function injectMailChimp(\Mailchimp $mc)
	{
		$this->mc = $mc;
	}

}