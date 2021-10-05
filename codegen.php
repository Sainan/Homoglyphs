<?php
$cpp = <<<EOC
namespace Sainan
{
	inline void transform_homoglyph(wchar_t& c)
	{
		switch(c)
		{
EOC;

$php = <<<EOC
<?php
function transform_homoglyphs(\$str)
{

EOC;

$latin = NULL;
$homoglyphs = [];
foreach(explode("\n", file_get_contents("data.txt")) as $line)
{
	$line = rtrim($line, "\r");
	if($line === "")
	{
		if($latin !== NULL)
		{
			$latin_char = '\'';
			if(in_array($latin, ['\'', '\\']))
			{
				$latin_char .= '\\';
			}
			$latin_char .= $latin;
			$latin_char .= '\'';

			foreach($homoglyphs as $homoglyph)
			{
				$cpp .= "\n\t\tcase L'{$homoglyph}':";
			}
			$cpp .= "\n\t\t\tc = L".$latin_char.";\n";
			$cpp .= "\t\t\tbreak;\n";

			$php .= "\t\$str = str_replace(['".join("', '", $homoglyphs)."'], {$latin_char}, \$str);\n";

			$latin = NULL;
			$homoglyphs = [];
		}
	}
	else if($latin === NULL)
	{
		$latin = $line;
	}
	else
	{
		array_push($homoglyphs, $line);
	}
}
$cpp .= "\t\t}\n\t}\n}\n";
$php .= "\treturn \$str;\n}\n";

file_put_contents("src/homoglyphs.hpp", $cpp);
file_put_contents("src/homoglyphs.php", $php);
