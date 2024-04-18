<?php

namespace Glhd\AnsiPants;

use Stringable;

class AnsiChar implements Stringable
{
	protected ?int $width = null;
	
	public function __construct(
		public string $value,
		/** @var \Glhd\AnsiPants\Flag[] */
		public array $flags = [],
	) {
	}
	
	public function width(): int
	{
		return $this->width ??= mb_strwidth($this->value);
	}
	
	public function is(AnsiChar $other, bool $ignore_style = false): bool
	{
		return $this->value === $other->value && ($ignore_style || $this->flags === $other->flags);
	}
	
	public function isNot(AnsiChar $other, bool $ignore_style = false): bool
	{
		return $this->value !== $other->value || (! $ignore_style && $this->flags !== $other->flags);
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
