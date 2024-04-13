<?php

namespace Glhd\AnsiPants;

class AnsiChar
{
	public function __construct(
		public string $char,
		public bool $bold,
		public bool $italic,
		public bool $underline,
		public ?string $foreground,
		public ?string $background,
	) {
	}
}
