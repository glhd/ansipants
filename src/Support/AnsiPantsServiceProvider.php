<?php

namespace Glhd\AnsiPants\Support;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Blade;
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
