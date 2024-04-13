<?php

namespace Glhd\AnsiPants\Support;

use Illuminate\Support\ServiceProvider;

class AnsiPantsServiceProvider extends ServiceProvider
{
	public function boot()
	{
	}
	
	public function register()
	{
		require_once __DIR__.'/helpers.php';
	}
}
