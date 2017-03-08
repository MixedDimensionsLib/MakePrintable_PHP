<?php

namespace MakePrintable;

use MakePrintable\Exceptions\FileNotFoundException;
use MakePrintable\Exceptions\RuntimeException;

/**
* This class makes sure to parse makeprintable's json config file into php object. 
*/
class ConfigProvider
{
	private $configPath;
	private $config;

	function __construct($configPath = __DIR__ . "/../Config/config.json")
	{
		$this->configPath = $configPath;

		if (!file_exists($this->configPath))
			throw new FileNotFoundException('Config file not found! please make sure to provide a valid json config file uri.');

		$config = json_decode(file_get_contents($configPath));
		if (empty($config))
			throw new RuntimeException('Invalid Config File! unable to parse json config into an object, please make sure to have a valid json object in your config file.');

		$this->config = $config;
	}

	public function getConfig()
	{
		return $this->config;
	}
}
