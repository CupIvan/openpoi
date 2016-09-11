<?php

class SupermarketParser extends Parser
{
	protected $type   = 'supermarket';

	protected function prepare(&$data)
	{
		$data['s1'] = @$data['opening_hours'];
		parent::prepare($data);
	}
}

