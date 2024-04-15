<?php

namespace Glhd\AnsiPants;

use Stringable;

class AnsiChar implements Stringable
{
	public function __construct(
		public string $value,
		/** @var \Glhd\AnsiPants\Flag[] */
		public array $flags = [],
	) {
	}
	
	public function withFlags(Flag ...$flags): static
	{
		return new static($this->value, $flags);
	}
	
	public function hasFlag(Flag $flag): bool
	{
		return in_array($flag, $this->flags, true);
	}
	
	public function __toString(): string
	{
		return $this->value;
	}
}
