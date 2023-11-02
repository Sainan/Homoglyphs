<?php
$homoglyphs = [];

// Parse data to populate $homoglyphs

$latin = NULL;
$arr = [];
foreach(explode("\n", file_get_contents("data.txt")) as $line)
{
	$line = rtrim($line, "\r");
	if($line === "")
	{
		if($latin !== NULL)
		{
			$homoglyphs[$latin] = $arr;

			$latin = NULL;
			$arr = [];
		}
	}
	else if($latin === NULL)
	{
		$latin = $line;
	}
	else
	{
		array_push($arr, $line);
	}
}

// Generate C++ code

$code = <<<EOC
namespace Sainan
{
	inline void transform_homoglyph(wchar_t& c)
	{
		switch(c)
		{
EOC;

foreach ($homoglyphs as $latin => $arr)
{
	$latin_char = '\'';
	if(in_array($latin, ['\'', '\\']))
	{
		$latin_char .= '\\';
	}
	$latin_char .= $latin;
	$latin_char .= '\'';

	foreach ($arr as $homoglyph)
	{
		$code .= "\n\t\tcase L'{$homoglyph}':";
	}
	$code .= "\n\t\t\tc = L".$latin_char.";\n";
	$code .= "\t\t\tbreak;\n";
}

$code .= "\t\t}\n\t}\n}\n";

file_put_contents("src/homoglyphs.hpp", $code);

// Generate Lua code

$code = "function transform_homoglyphs(str)\n";

foreach ($homoglyphs as $latin => $arr)
{
	$latin_char = '\'';
	if(in_array($latin, ['\'', '\\']))
	{
		$latin_char .= '\\';
	}
	$latin_char .= $latin;
	$latin_char .= '\'';

	foreach ($arr as $homoglyph)
	{
		$code .= "\tstr = str:gsub(\"".$homoglyph."\", {$latin_char})\n";
	}
}

$code .= "\treturn str\nend\n";

file_put_contents("src/homoglyphs.lua", $code);

// Generate PHP code

$code = <<<EOC
<?php
function transform_homoglyphs(\$str)
{

EOC;

foreach ($homoglyphs as $latin => $arr)
{
	$latin_char = '\'';
	if(in_array($latin, ['\'', '\\']))
	{
		$latin_char .= '\\';
	}
	$latin_char .= $latin;
	$latin_char .= '\'';

	$code .= "\t\$str = str_replace(['".join("', '", $arr)."'], {$latin_char}, \$str);\n";
}

$code .= "\treturn \$str;\n}\n";

file_put_contents("src/homoglyphs.php", $code);
