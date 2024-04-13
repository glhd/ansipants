<?php

namespace Glhd\AnsiPants;

class AnsiChar
{
	public function __construct(
		public string $value,
		/** @var \Glhd\AnsiPants\Flag[] */
		public array $flags,
	) {
	}
	
	public function hasFlag(Flag $flag): bool
	{
		return in_array($flag, $this->flags, true);
	}
}
