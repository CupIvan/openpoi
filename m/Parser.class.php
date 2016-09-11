<?php

class Parser
{
	private $context = NULL;
	private $method  = 'GET';
	private $host    = NULL;
	private $cookies = [];
	private $data    = NULL;
	private $stat    = ['numSaved'=>0, 'numUpdated'=>0, 'numPages'=>0, 'numDownloaded'=>0];

	protected function start() {}
	public function stat() { return $this->stat; }

	protected function download_html($url)
	{
		if ($url[0] == '/') $url = $this->host.$url;
		else $this->host = 'http://'.parse_url($url, PHP_URL_HOST);

		$md5 = md5($url);
		$this->stat['numPages']++;
		$fname = './cache/'.$this->brand.'/'.$md5[0]."/$md5.html";
		if (!is_dir($x = dirname($fname))) mkdir($x, 0777, true);
		if (time() - @filemtime($fname) < 24*3600)
			return file_get_contents($fname);

		$html = file_get_contents($url, false, $this->context);
		if (stripos($html, 'charset=windows-1251')) $html = iconv('cp1251', 'utf-8', $html);
		if ($html)
			file_put_contents($fname, $html);

		$this->stat['numDownloaded']++;
		return $html;
	}
	protected function setCookie($name, $value)
	{
		$this->cookies[$name] = $value;
		$this->updateContext();
	}
	private function updateContext()
	{
		$this->context = stream_context_create([
			'http' => [
				'method' => $this->method,
				'header' => 'Cookie: '.http_build_query($this->cookies)."\r\n",
			]
		]);
	}
	protected function parseOpeningHours($st)
	{
		return $st;
	}
	protected function save($data)
	{
		if (!empty($data[$x='opening_hours'])) $data[$x] = $this->parseOpeningHours($data[$x]);
		$this->stat['numSaved']++;
		$this->prepare($data);
		if ($this->is_changed($data))
		{
			mysql::insert_duplicate('poi', $data);
			$this->stat['numUpdated']++;
		}
	}
	protected function substr($st, $st_from, $st_to)
	{
		$from = strpos($st, $st_from);
		if  ($from === false) return NULL;
		else $from += strlen($st_from);
		$to   = strpos($st, $st_to, $from);
		if (strlen($st) < 100)
		echo "$st_from: $from - $to\n$st\n";
		return substr($st, $from, $to - $from);
	}
	protected function prepare(&$data)
	{
		$data['id_local'] = $data['id'];
		$data = array_intersect_key($data, array_fill_keys(['id_local','name',
			'city','lat','lon','s1','s2','s3','s4','i1','i2','i3','i4'], 1));
		$data['brand'] = $this->brand;
		$data['type']  = $this->type;
		$data['md5']   = md5(serialize($data));
	}
	private function is_changed(&$data)
	{
		$old = $this->getData($data['id_local']);
		if (@$old['md5'] != @$data['md5'])
		{
			$data['timeUpdate'] = date('Y-m-d H:i:s');
			return true;
		}
		return false;
	}
	private function getData($id)
	{
		if (is_null($this->data))
		{
			$this->data = [];
			$sql = mysql::prepare('`brand` = ?s', $this->brand);
			foreach (mysql::search('poi', $sql, 'id_local, md5') as $a)
				$this->data[$a['id_local']] = $a;
		}
		return isset($this->data[$id]) ? $this->data[$id] : [];
	}
}
