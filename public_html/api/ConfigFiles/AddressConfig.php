<?php

require_once "Config.php";

class AddressConfig extends Config
{
	protected function getUserName(): string
	{
		return 'addressbot';
	}

	protected function getPassword(): string
	{
		return "d80c9bf910f144738ef983724bc04bd6bd3f17c5c83ed57bedee1b1b9278e811";
	}
}
