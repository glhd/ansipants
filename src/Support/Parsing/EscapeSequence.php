<?php

namespace Glhd\AnsiPants\Support\Parsing;

class EscapeSequence implements Token
{
	public function __construct(
		public string $value
	) {
	}
}
