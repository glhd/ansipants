<div style="float: right;">
	<a href="https://github.com/glhd/ansipants/actions" target="_blank">
		<img 
			src="https://github.com/glhd/ansipants/workflows/PHPUnit/badge.svg" 
			alt="Build Status" 
		/>
	</a>
	<a href="https://codeclimate.com/github/glhd/ansipants/test_coverage" target="_blank">
		<img 
			src="https://api.codeclimate.com/v1/badges/04761c68fd9dc46cf608/test_coverage" 
			alt="Coverage Status" 
		/>
	</a>
	<a href="https://packagist.org/packages/glhd/ansipants" target="_blank">
        <img 
            src="https://poser.pugx.org/glhd/ansipants/v/stable" 
            alt="Latest Stable Release" 
        />
	</a>
	<a href="./LICENSE" target="_blank">
        <img 
            src="https://poser.pugx.org/glhd/ansipants/license" 
            alt="MIT Licensed" 
        />
    </a>
    <a href="https://twitter.com/inxilpro" target="_blank">
        <img 
            src="https://img.shields.io/twitter/follow/inxilpro?style=social" 
            alt="Follow @inxilpro on Twitter" 
        />
    </a>
</div>

# ANSI Pants 👖💫

## Installation

```shell
composer require glhd/ansipants
```

## Usage

You can instantiate a new ANSI string using the `ansi()` helper, with `new AnsiString()`,
or with `AnsiString::make()`. All string manipulation functions can be chained, just like
the Laravel `Stringable` class. Where appropriate, you can pass an additional `ignore_style: true`
argument into a function to make that function ignore the ANSI styles that are applied
(like color or font style).

An example:

```php
ansi("\e[1mHello💥 \e[3mwo\e[0mrld")
  ->append(" 🥸🥸🥸")
  ->padLeft(100)
  ->wordwrap();
```

## Resources

- https://notes.burke.libbey.me/ansi-escape-codes/
- https://en.wikipedia.org/wiki/ANSI_escape_code
