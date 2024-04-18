<?php

namespace Glhd\AnsiPants\Support;

use Glhd\AnsiPants\AnsiString;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Stringable;

class AnsiPantsServiceProvider extends ServiceProvider
{
	public function register(): void
	{
		require_once __DIR__.'/helpers.php';
	}
	
	public function boot(): void
	{
		Stringable::macro('ansi', function() {
			return new AnsiString((string) $this);
		});
	}
}
