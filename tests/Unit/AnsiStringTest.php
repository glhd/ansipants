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
	
	public function test_pad_left(): void
	{
		$parsed = new AnsiString("\e[1mHello \e[0mworld");
		$expected = "\e[1m    Hello \e[0mworld";
		
		$this->assertEquals($expected, (string) $parsed->padLeft(15));
	}
	
	public function test_pad_right(): void
	{
		$parsed = new AnsiString("\e[1mHello \e[0mworld");
		$expected = "\e[1mHello \e[0mworld    ";
		
		$this->assertEquals($expected, (string) $parsed->padRight(15));
	}
	
	public function test_pad_both(): void
	{
		$parsed = new AnsiString("\e[1mHello \e[0mworld");
		$expected = "\e[1m  Hello \e[0mworld  ";
		
		$this->assertEquals($expected, (string) $parsed->padBoth(15));
	}
	
	public function test_word_wrap(): void
	{
		$input = "The \e[1mquick\e[0m \e[33mbrown fox \e[3mjumps\e[0m over the lazy dog";
		
		$expected = <<<EOF
		The \e[1mquick\e[0m
		\e[33mbrown fox\e[0m
		\e[33m\e[3mjumps\e[0m over
		the lazy
		dog
		EOF;

		$parsed = new AnsiString($input);
		
		$this->assertEquals($expected, (string) $parsed->wordwrap(10));
	}

	public function test_it_cuts_long_words_when_wrapping(): void
	{
		$input = "Cut long words";

		$expected = <<<EOF
		Cut
		Lon
		g w
		ord
		s
		EOF;

		$parsed = new AnsiString($input);

		$this->assertEquals($expected, (string) $parsed->wordwrap(3, cut_long_words: true));
	}

	public function test_it_wraps_wide_characters(): void
	{
		$input = '🔥 🔥 あ あ';

		$expected = <<<EOF
		🔥
		🔥
		あ
		あ
		EOF;

		$parsed = new AnsiString($input);

		$this->assertEquals($expected, (string) $parsed->wordwrap(3));
	}
}
