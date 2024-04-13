<?php

namespace Glhd\AnsiPants;

use Glhd\AnsiPants\Support\Parsing\Tokenizer;
use Stringable;

class AnsiString implements Stringable
{
	protected array $chars = [];
	
	protected array $flags = [];
	
	public function __construct(string $input)
	{
		foreach (Tokenizer::make($input)->parse() as $token) {
			
		}
	}
	
	public function __toString(): string
	{
		
		// TODO: Implement __toString() method.
	}
}
