<?php

use Glhd\AnsiPants\AnsiString;

if (! function_exists('ansi')) {
	function ansi(string $value): AnsiString
	{
		return new AnsiString($value);
	}
}
