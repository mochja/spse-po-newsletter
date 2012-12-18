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

	public function __construct(\Nette\Database\Connection $db)
	{
		$this->db = $db;
	}

	public function get($id)
	{
		return $this->table->get($id);
	}

	public function get_articles($id, $type=NULL)
	{
		$r = $this->db->table('newsletter_article')->where('id', $id)->order('pos DESC');
		if ( $type!== NULL)
			$r->where('type', $type);
		return $r;
	}

	public function update($id, $data)
	{
		return $this->table->find($id)->update($data);
	}

	public function insert($values)
	{
		return $this->table->insert($values);
	}

	public function add_article($values)
	{
		return $this->db->table('newsletter_article')->insert($values);
	}

	public function del_article($id)
	{
		return $this->db->table('newsletter_article')->find($id)->delete();
	}

	public function edit_article($id, $values)
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
	public static function build_number($int)
	{
		return floor($int / 100) . "/" . "20" . ($int-floor($int / 100)*100);
	}

	public static function build_datetime($int)
	{
		return new \DateTime("20" . ($int-floor($int / 100)*100)."-".floor($int / 100)."-01");
	}

	public static function gen_new_numbers()
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

	public static function date_to_number(\DateTime $date)
	{
		return (int)($date->format("n").$date->format("y"));
	}

}