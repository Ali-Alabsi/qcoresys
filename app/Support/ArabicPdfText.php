<?php

namespace App\Support;

/**
 * Shape Arabic text for DomPDF (which lacks complex-script shaping).
 * Logic adapted from khaled.alshamaa/ar-php utf8Glyphs.
 */
class ArabicPdfText
{
    /** @var array<string, mixed>|null */
    private static ?array $glyphs = null;

    private static string $vowels = 'ًٌٍَُِّْ';

    public static function shape(?string $text, bool $hinduDigits = false): string
    {
        if ($text === null || $text === '') {
            return '';
        }

        if (! preg_match('/\p{Arabic}/u', $text)) {
            return $text;
        }

        self::boot();

        $lines = preg_split("/\r\n|\n|\r/", $text) ?: [$text];
        $out = [];

        foreach ($lines as $line) {
            $out[] = self::shapeLine($line);
        }

        $result = implode("\n", $out);

        if ($hinduDigits) {
            $result = strtr($result, [
                '0' => '٠', '1' => '١', '2' => '٢', '3' => '٣', '4' => '٤',
                '5' => '٥', '6' => '٦', '7' => '٧', '8' => '٨', '9' => '٩',
            ]);
        }

        return $result;
    }

    private static function boot(): void
    {
        if (self::$glyphs !== null) {
            return;
        }

        $path = base_path('vendor/khaled.alshamaa/ar-php/src/data/ar_glyphs.json');
        $json = is_file($path) ? file_get_contents($path) : false;
        self::$glyphs = $json ? (json_decode($json, true) ?: []) : [];
    }

    private static function shapeLine(string $str): string
    {
        $pairs = [];
        foreach (['َ', 'ً', 'ُ', 'ٌ', 'ِ', 'ٍ'] as $haraka) {
            $pairs["ّ{$haraka}"] = "{$haraka}ّ";
        }
        $str = strtr($str, $pairs);

        // Process whole line as RTL Arabic-capable text for DomPDF documents.
        return self::convert($str);
    }

    private static function convert(string $str): string
    {
        $crntChar = null;
        $prevChar = null;
        $nextChar = null;
        $output = '';
        $number = '';
        $chars = [];

        $openRange = ')]>}';
        $closeRange = '([<{';
        $len = mb_strlen($str);

        for ($i = 0; $i < $len; $i++) {
            $chars[] = mb_substr($str, $i, 1);
        }

        $max = count($chars);

        for ($i = $max - 1; $i >= 0; $i--) {
            $crntChar = $chars[$i];
            $form = 0;

            if ($i > 0) {
                $prevChar = $chars[$i - 1];
                if (mb_strpos(self::$vowels, $prevChar) !== false && $i > 1) {
                    $prevChar = $chars[$i - 2];
                    if (mb_strpos(self::$vowels, $prevChar) !== false && $i > 2) {
                        $prevChar = $chars[$i - 3];
                    }
                }
            } else {
                $prevChar = ' ';
            }

            if (is_numeric($crntChar)) {
                $number = $crntChar.$number;
                continue;
            }

            if (strlen($number) > 0) {
                $output .= $number;
                $number = '';
            }

            if (mb_strpos($openRange.$closeRange, $crntChar) !== false) {
                $output .= ($closeRange.$openRange)[mb_strpos($openRange.$closeRange, $crntChar)];
                continue;
            }

            if (ord($crntChar) < 128) {
                $output .= $crntChar;
                $nextChar = $crntChar;
                continue;
            }

            if (
                $crntChar === 'ل' && isset($nextChar)
                && mb_strpos('آأإا', $nextChar) !== false
                && isset(self::$glyphs[$crntChar.$nextChar])
            ) {
                $output = substr($output, 0, strlen($output) - 8);
                if (isset(self::$glyphs[$prevChar]['prevLink']) && self::$glyphs[$prevChar]['prevLink'] == true) {
                    $output .= '&#x'.self::$glyphs[$crntChar.$nextChar][1].';';
                } else {
                    $output .= '&#x'.self::$glyphs[$crntChar.$nextChar][0].';';
                }
                continue;
            }

            if (mb_strpos(self::$vowels, $crntChar) !== false) {
                continue;
            }

            if ($prevChar && isset(self::$glyphs[$prevChar]) && (self::$glyphs[$prevChar]['prevLink'] ?? false) == true) {
                $form++;
            }

            if ($nextChar && isset(self::$glyphs[$nextChar]) && (self::$glyphs[$nextChar]['nextLink'] ?? false) == true) {
                $form += 2;
            }

            if (isset(self::$glyphs[$crntChar][$form])) {
                $output .= '&#x'.self::$glyphs[$crntChar][$form].';';
            } else {
                $output .= $crntChar;
            }

            $nextChar = $crntChar;
        }

        if (strlen($number) > 0) {
            $output .= $number;
        }

        return html_entity_decode($output, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
}
