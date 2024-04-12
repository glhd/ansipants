<?php

namespace Glhd\AnsiPants;

use Glhd\AnsiPants\Support\AnsiParser;
use Stringable;

class AnsiString implements Stringable
{
	protected array $chars = [];
	
	protected array $flags = [];
	
	public function __construct(string $input)
	{
		$parser = new AnsiParser($input);
		[$this->chars, $this->flags] = $parser->parse();
	}
	
	public function __toString(): string
	{
		// TODO: Implement __toString() method.
	}
}
