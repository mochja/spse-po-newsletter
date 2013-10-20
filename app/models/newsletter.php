<?php

namespace spse\newsletter\model;

/**
 * @author Jan Mochnak <janmochnak@gmail.com>
 * @package newsletter
 */

class Newsletter extends \Nette\Object
{

	private $db;

	private $articleTypes = array();

	private static $months = array(
		'Január',
		'Február',
		'Marec',
		'Apríl',
		'Máj',
		'Jún',
		'Júl',
		'August',
		'September',
		'Október',
		'November',
		'December'
	);

	public function __construct(\Nette\Database\Connection $db)
	{
		$this->db = $db;
	}

	public function get($id)
	{
        if (empty($id)) {
            throw new \ErrorException('id wasnt set.');
        }
		$newsletter = $this->table->find($id)->fetch();
		if (!$newsletter) {
			throw new \FatalErrorException('Newsletter with id = '.$id.' wasn\'t found.');
		}
		return $newsletter;
	}

	public function getArticles($id, $type=NULL)
	{
		$result = $this->db->table('newsletter_article')->where('id', $id)->order('pos DESC');
		if ( $type !== NULL )
			$result->where('type', $type);
		return $result;
	}

	public function getArticleTypes()
	{
		if (empty($this->articleTypes)) {
			$this->articleTypes = $this->db->table('newsletter_article_types')->fetchPairs('id', 'title');
		}
		return $this->articleTypes;
	}

	public function getArticleType($type)
	{
		return array_search($type, $this->getArticleTypes());
	}

	public function update($id, $data)
	{
		return $this->table->find($id)->update($data);
	}

	public function insert($values)
	{
		return $this->table->insert($values);
	}

	public function addArticle($values)
	{
		return $this->db->table('newsletter_article')->insert($values);
	}

	public function delArticle($id)
	{
		return $this->db->table('newsletter_article')->find($id)->delete();
	}

	public function editArticle($id, $values)
	{
		return $this->db->table('newsletter_article')->find($id)->update($values);
	}

	public function generateTemplate($id)
	{
		$newsletter = $this->get((int) $id);

		$template = new \Nette\Templating\FileTemplate(__DIR__.'/../AdminModule/templates/email.latte');
		$template->registerFilter(new \Nette\Latte\Engine);
		$template->registerHelperLoader('Nette\Templating\Helpers::loader');

		$number = $template->number = self::buildNumber($newsletter->number);

		$template->articles = $this->db->table('newsletter_article')
			->where('type', 0)
			->where('newsletter_id', (int) $id)
			->order('pos, id');

		$template->classes = $this->db->table('newsletter_article')
			->where('type', 2)
			->where('newsletter_id', (int) $id)
			->order('pos, id')
			->select('title');

		$template->tops = $this->db->table('newsletter_article')
			->where('type', 1)
			->where('newsletter_id', (int) $id)
			->order('pos, id')
			->select('title');

		return (string) $template;
	}

	public function getTable()
	{
		return $this->db->table('newsletter');
	}

	/**
	 * 125 -> 2012-5
	 */
	public static function buildNumber($int)
	{
		return '20'.substr($int, 0, 2).'-'.str_pad(substr($int, 2), 2, '0', STR_PAD_LEFT);
	}

	/**
	 * 125 - 5/2013
	 * defined as nformat helper in template, see basePresenter
	 */
	public static function buildFriendlyNumber($int)
	{
		return substr($int, 2).'/'.'20'.substr($int, 0, 2);
	}

	public static function buildDatetime($int)
	{
		return new \DateTime(self::buildNumber($int)."-01");
	}

	public static function dateToNumber(\DateTime $date)
	{
		return (int)($date->format("y").$date->format("n"));
	}

	public static function getMonthName($month)
	{
		return self::$months[$month];
	}

	/**
	 * from 9 to 6 next year
	 */
	public static function getSchoolYear($date)
	{
		$month = $date->format('n');
		if ($month >= 9 && $month <= 12) {
			return $date->format('Y');
		} else {
			return $date->format('Y') - 1;
		}
	}

}