<?php

namespace Glhd\AnsiPants\Tests\Unit;

use Glhd\AnsiPants\AnsiString;
use Glhd\AnsiPants\Tests\TestCase;

class AnsiStringTest extends TestCase
{
	public function test_it_can_be_instantiated_from_a_string_with_ansi_sequences(): void
	{
		$original = "\e[1mHello💥 \e[3mwo\e[0mrld 🥸";
		$parsed = new AnsiString($original);
		
		$this->assertEquals($original, (string) $parsed);
	}
}
