<?php

class BinbankParser extends BankParser
{
	private   $url   = 'https://www.binbank.ru/branches/offices/list/';
	protected $brand = 'Бинбанк';

	public function start()
	{
		foreach ($this->getCityList() as $id => $city)
		{
			$data = $this->getData($id);
			foreach ($data as $object)
			{
				$object['city'] = $city;
				$this->save($object);
			}
		}
	}
	public function getCityList()
	{
		$res = [];
		$html = $this->download_html($this->url);
		$html = $this->substr($html, 'mobileCurrentCity', '</ul');
		if (preg_match_all('#data-rel.+?(\d+).+?>([^<]+)#s', $html, $m, PREG_SET_ORDER))
			foreach ($m as $m) $res[$m[1]] = $m[2];
		return $res;
	}
	public function getData($id = NULL)
	{
		$res = [];
		$url = $this->url;
		if (!is_null($id)) { $this->setCookie('BITRIX_SM_CITY_ID', $id); $url .= '?id='.$id; }
		$html = $this->download_html($url);
		$html = $this->substr($html, '<!--office list-->', '<!--/office list-->');
		foreach (explode('itemscope', $html) as $st)
			if ($x = $this->parseItem($st)) $res[] = $x;
		return $res;
	}
	protected function parseItem($st)
	{
		$res = [];
		if (preg_match('#mapx=\D+([\d.]+).+?mapy=\D+([\d.]+).+?id=\D+(\d+)#s', $st, $m))
			$res += ['lat'=>$m[1], 'lon'=>$m[2], 'id'=>$m[3]];
		if (empty($res)) return NULL;

		if ($x = $this->substr($st, 'office_list_name">', '</div>'))
			$res['name'] = str_replace('&nbsp;', ' ', $x);
		if ($x = $this->substr($st, 'openingHours">', '</td'))
			$res['opening_hours'] = $this->parseHours($x);
		return $res;
	}
	protected function parseHours($st)
	{
		$st = mb_strtolower($st);
		$st = explode('<br', $st)[0];
		$st = preg_replace('/лица[: ]/', 'лицами', $st);
		$x = strpos($st, $t='лицами'); if ($x) $st = substr($st, $x + strlen($t));
		$x = strpos($st, $t='юр'); if ($x) $st = substr($st, 0, $x);
		$st = preg_replace('/^[^а-я0-9]+/', '', $st);
		return strip_tags($st);
	}
}
