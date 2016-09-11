<?php

class EldoradoParser extends SupermarketParser
{
	private   $url   = 'http://www.eldorado.ru/info/shops/cities/';
	protected $brand = 'Эльдорадо';

	public function start()
	{
		foreach ($this->getCityList() as $id => $city)
		{
			$data = $this->getData($id);
			foreach ($data as $object)
			{
				if ($id == 11324 & $object['id'] == 1195202)   continue; // этот id уже есть в Химках
				if ($id == 11324 & $object['id'] == 364289463) continue; // этот id уже есть в Мытищах
				$object['city'] = $city;
				$this->save($object);
			}
		}
	}
	public function getCityList()
	{
		$res = [];
		$html = $this->download_html($this->url);
		if (preg_match_all('#/info/shops/(\d+)/">([^<]+)#i', $html, $m, PREG_SET_ORDER))
			foreach ($m as $m) $res[$m[1]] = $m[2];
		return $res;
	}
	public function getData($id)
	{
		$url = $this->url;
		$html = $this->download_html("/info/shops/$id/");

		// координаты
		$coords = [];
		if ($st = $this->substr($html, 'function BX_SetPlacemarks_MAP', '</script>'))
		{
			if (preg_match_all('#LON\D+([\d.]+)\D+LAT\D+([\d.]+).+?/info/shops/\d+/(\d+)#', $st, $m, PREG_SET_ORDER))
			foreach ($m as $a)
				$coords[$a[3]] = ['lat'=>$a[2], 'lon'=>$a[1]];
		}

		$html = $this->substr($html, 'obFilter.shop = [', ' ];');
		$html =  str_replace("'", '"', "[$html]");
		$html = preg_replace('/([a-z_]+):/i', '"$1":', $html);
		$a = json_decode($html, true);
		if (!$a) return [];
		foreach ($a as $i => $_)
		{
			$a[$i]['opening_hours'] = $a[$i]['time_work'];
			if (!empty($coords[$a[$i]['id']])) $a[$i] += $coords[$a[$i]['id']];
		}

		return $a;
	}
}
