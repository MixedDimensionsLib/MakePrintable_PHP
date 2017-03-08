<?php

namespace MakePrintable\Exceptions;

class FileNotFoundException extends \Exception
{
	public function __construct($message)
	{
		$this->message = $message;
	}
}
