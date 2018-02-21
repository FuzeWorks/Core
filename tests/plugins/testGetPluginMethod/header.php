<?php

namespace FuzeWorks\Plugins;
use FuzeWorks\PluginInterface;

class TestGetPluginMethodHeader implements PluginInterface
{
	public function init()
	{
	}

	public function getPlugin()
	{
		return 'test_string';
	}
}