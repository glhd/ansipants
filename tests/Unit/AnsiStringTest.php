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
	
	public function test_explode(): void
	{
		$input = "The \e[1mquick\e[0m \e[33mbrown fox \e[3mjumps\e[0m over the lazy dog";
		
		$expected = [
			"The",
			"\e[1mquick",
			"\e[33mbrown",
			"\e[33mfox",
			"\e[33m\e[3mjumps",
			"over",
			"the",
			"lazy",
			"dog",
		];
		
		$results = AnsiString::make($input)
			->explode(' ')
			->map(fn(AnsiString $line) => (string) $line)
			->all();
		
		$this->assertEquals($expected, $results);
	}
	
	public function test_width(): void
	{
		$string = new AnsiString("\e[1m😎😎😎\e[0m");
		
		$this->assertEquals(3, $string->length());
		$this->assertEquals(6, $string->width());
	}
	
	public function test_starts_with(): void
	{
		$string = new AnsiString("\e[1m😎😎😎 hello world\e[0m");
		
		$this->assertTrue($string->startsWith("\e[1m😎"));
		$this->assertTrue($string->startsWith("\e[1m😎😎😎"));
		$this->assertTrue($string->startsWith("\e[1m😎😎😎 hello"));
		
		$this->assertFalse($string->startsWith("😎"));
		$this->assertFalse($string->startsWith("😎😎😎"));
		$this->assertFalse($string->startsWith("😎😎😎 hello"));
		
		$this->assertTrue($string->startsWith("😎", true));
		$this->assertTrue($string->startsWith("😎😎😎", true));
		$this->assertTrue($string->startsWith("😎😎😎 hello", true));
		
		$this->assertTrue($string->startsWith("\e[3m😎", true));
		$this->assertTrue($string->startsWith("\e[3m😎😎😎", true));
		$this->assertTrue($string->startsWith("\e[3m😎😎😎 hello", true));
	}
	
	public function test_ends_with(): void
	{
		$string = new AnsiString("😎😎😎 hello \e[1mworld");
		
		$this->assertTrue($string->endsWith("\e[1mworld"));
		$this->assertFalse($string->endsWith("world"));
		$this->assertTrue($string->endsWith("world", true));
		$this->assertTrue($string->endsWith("\e[3mworld", true));
	}
}
