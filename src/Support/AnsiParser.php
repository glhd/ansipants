<?php

namespace Glhd\AnsiPants\Support;

use Glhd\AnsiPants\AnsiString;
use Illuminate\Support\ServiceProvider;

class AnsiParser
{
	public function __construct(
		protected string $input
	) {
	}
	
	public function parse(): array
	{
		// FIXME
	}
}
