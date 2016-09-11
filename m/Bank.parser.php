<?php

class BankParser extends Parser
{
	protected $type   = 'bank';

	protected function prepare(&$data)
	{
		$data['s1'] = @$data['opening_hours'];
		parent::prepare($data);
	}
}

