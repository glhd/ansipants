<?php

namespace Glhd\AnsiPants\Support\Parsing;

class Text implements Token
{
	public function __construct(
		public string $value
	) {
	}
}
