<?php

namespace spse\newsletter\model;

/**
 *
 *
 * @author Jan Mochnak <janmochnak@gmail.com>
 * @package newsletter
 */

class Newsletter extends \Nette\Object
{

	private $db;

	private $articleTypes = array();

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


	public function getTable()
	{
		return $this->db->table('newsletter');
	}

	/**
	 * 512 -> 5/2012
	 * defined as nformat helper in template, see basePresenter
	 */
	public static function buildNumber($int)
	{
		return floor($int / 100) . "/" . "20" . ($int-floor($int / 100)*100);
	}

	public static function buildDatetime($int)
	{
		return new \DateTime("20" . ($int-floor($int / 100)*100)."-".floor($int / 100)."-01");
	}

	public static function genNewNumbers()
	{
		$d = new \DateTime();
		$date = $d->modify("-1 month");
		$list = array();
		for ( $i = 0; $i < 30; $i++ ) {
			$list[(int) self::date_to_number($date)] = $date->format("n")."/".$date->format("Y");
			$date->modify("+1 month");
		}
		return $list;
	}

	public static function dateToNumber(\DateTime $date)
	{
		return (int)($date->format("n").$date->format("y"));
	}

}