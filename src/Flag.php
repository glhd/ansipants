<?php

namespace Glhd\AnsiPants;

enum Flag: int
{
	case Reset = 0;
	case Bold = 1;
	case Faint = 2;
	case Italic = 3;
	case Underline = 4;
	case Normal = 22;
	case NotItalic = 23;
	case NotUnderline = 24;
	case BlackForeground = 30;
	case RedForeground = 31;
	case GreenForeground = 32;
	case YellowForeground = 33;
	case BlueForeground = 34;
	case MagentaForeground = 35;
	case CyanForeground = 36;
	case WhiteForeground = 37;
	case DefaultForeground = 39;
	case BlackBackground = 40;
	case RedBackground = 41;
	case GreenBackground = 42;
	case YellowBackground = 43;
	case BlueBackground = 44;
	case MagentaBackground = 45;
	case CyanBackground = 46;
	case WhiteBackground = 47;
	case DefaultBackground = 49;
	case BrightBlackForeground = 90;
	case BrightRedForeground = 91;
	case BrightGreenForeground = 92;
	case BrightYellowForeground = 93;
	case BrightBlueForeground = 94;
	case BrightMagentaForeground = 95;
	case BrightCyanForeground = 96;
	case BrightWhiteForeground = 97;
	case BrightBlackBackground = 100;
	case BrightRedBackground = 101;
	case BrightGreenBackground = 102;
	case BrightYellowBackground = 103;
	case BrightBlueBackground = 104;
	case BrightMagentaBackground = 105;
	case BrightCyanBackground = 106;
	case BrightWhiteBackground = 107;
	
	public function isForegroundColor(): bool
	{
		return ($this->value >= 30 && $this->value < 40)
			|| ($this->value >= 90 && $this->value < 100);
	}
	
	public function isBackgroundColor(): bool
	{
		return ($this->value >= 40 && $this->value < 50)
			|| ($this->value >= 100 && $this->value < 110);
	}
	
	public function getEscapeSequence(): string
	{
		return "\e[{$this->value}m";
	}
	
	public function getInverseEscapeSequence(): string
	{
		return $this->inverse()?->getEscapeSequence() ?? '';
	}
	
	public function overrides(Flag $flag): bool
	{
		if ($this === self::Reset) {
			return true;
		}
		
		if ($this->isForegroundColor()) {
			return $flag->isForegroundColor();
		}
		
		if ($this->isBackgroundColor()) {
			return $this->isBackgroundColor();
		}
		
		foreach ($this->pairs() as $pair) {
			if ($this === $pair[0] && $flag === $pair[1]) {
				return true;
			}
			if ($this === $pair[1] && $flag === $pair[0]) {
				return true;
			}
		}
		
		return false;
	}
	
	public function inverse(): ?Flag
	{
		if ($this->isForegroundColor()) {
			return self::DefaultForeground;
		}
		
		if ($this->isBackgroundColor()) {
			return self::DefaultBackground;
		}
		
		foreach ($this->pairs() as $pair) {
			if ($this === $pair[0]) {
				return $pair[1];
			}
		}
		
		return null;
	}
	
	protected function pairs(): array
	{
		return [
			[self::Bold, self::Normal],
			[self::Faint, self::Normal],
			[self::Italic, self::NotItalic],
			[self::Underline, self::NotUnderline],
		];
	}
}
