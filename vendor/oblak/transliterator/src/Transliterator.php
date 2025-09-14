<?php

namespace Oblak;

/**
 * Transliterator class
 */
class Transliterator
{
    /**
     * Replace pairs for cyrilic to latin conversion
     *
     * @var string[]
     */
    private $replacePairs = [
        "А"  => "A",
        "Б"  => "B",
        "В"  => "V",
        "Г"  => "G",
        "Д"  => "D",
        "Ђ"  => "Đ",
        "Е"  => "E",
        "Ж"  => "Ž",
        "З"  => "Z",
        "И"  => "I",
        "Ј"  => "J",
        "К"  => "K",
        "Л"  => "L",
        "Љ"  => "LJ",
        "М"  => "M",
        "Н"  => "N",
        "Њ"  => "NJ",
        "О"  => "O",
        "П"  => "P",
        "Р"  => "R",
        "С"  => "S",
        "Ш"  => "Š",
        "Т"  => "T",
        "Ћ"  => "Ć",
        "У"  => "U",
        "Ф"  => "F",
        "Х"  => "H",
        "Ц"  => "C",
        "Ч"  => "Č",
        "Џ"  => "DŽ",
        "Ш"  => "Š",
        "а"  => "a",
        "б"  => "b",
        "в"  => "v",
        "г"  => "g",
        "д"  => "d",
        "ђ"  => "đ",
        "е"  => "e",
        "ж"  => "ž",
        "з"  => "z",
        "и"  => "i",
        "ј"  => "j",
        "к"  => "k",
        "л"  => "l",
        "љ"  => "lj",
        "м"  => "m",
        "н"  => "n",
        "њ"  => "nj",
        "о"  => "o",
        "п"  => "p",
        "р"  => "r",
        "с"  => "s",
        "ш"  => "š",
        "т"  => "t",
        "ћ"  => "ć",
        "у"  => "u",
        "ф"  => "f",
        "х"  => "h",
        "ц"  => "c",
        "ч"  => "č",
        "џ"  => "dž",
        "ш"  => "š",
        "Ња" => "Nja",
        "Ње" => "Nje",
        "Њи" => "Nji",
        "Њо" => "Njo",
        "Њу" => "Nju",
        "Ља" => "Lja",
        "Ље" => "Lje",
        "Љи" => "Lji",
        "Љо" => "Ljo",
        "Љу" => "Lju",
        "Џа" => "Dža",
        "Џе" => "Dže",
        "Џи" => "Dži",
        "Џо" => "Džo",
        "Џу" => "Džu",
    ];

    /**
     * Additional replace pairs for cyrilic to cut latin conversion
     *
     * @var array
     */
    private $cutReplacePairs = [
        'Ђ'   => 'Dj',
        'Ж'   => 'Z',
        'Ч'   => 'C',
        'Ћ'   => 'C',
        'Џ'   => 'Dz',
        'Ш'   => 'S',
        'ђ'   => 'dj',
        'ж'   => 'z',
        'ч'   => 'c',
        'ћ'   => 'c',
        'џ'   => 'dz',
        'ш'   => 's',
        'Џа'   => 'Dza',
        'Џе'   => 'Dze',
        'Џи'   => 'Dzi',
        'Џо'   => 'Dzo',
        'Џу'   => 'Dzu',
    ];

    /**
     * Replace pairs for latin to cut latin conversion
     *
     * @var array
     */
    private $cutLatinChars = [
        'DŽ' => 'Dz',
        'Dž' => 'Dz',
        'dž' => 'dz',
        'Č'  => 'C',
        'č'  => 'c',
        'Ć'  => 'C',
        'ć'  => 'c',
        'Đ'  => 'Dj',
        'đ'  => 'dj',
        'Š'  => 'S',
        'š'  => 's',
        'Ž'  => 'Z',
        'ž'  => 'z',
    ];

    /**
     * Instance of Transliterator
     *
     * @var Transliterator
     */
    private static $instance = null;

    /**
     * Private constructor
     */
    private function __construct()
    {
    }

    /**
     * Get the class instance
     *
     * @return Transliterator
     */
    public static function instance()
    {
        if (self::$instance === null) {
            self::$instance = new Transliterator();
        }

        return self::$instance;
    }

    /**
     * Converts cyrilic string to latin
     *
     * @param  string $text Cyrilic string to convert
     * @return string       Latin string
     */
    public function cirToLat($text = '')
    {
        return strtr($text, $this->replacePairs);
    }

    /**
     * Converts latin string to cyrilic
     *
     * @param  string $text Latin string to convert
     * @return string       Cyrilic string
     */
    public function latToCir($text = '')
    {
        return strtr($text, array_flip($this->replacePairs));
    }

    /**
     * Converts cyrilic string to latin with cut chars
     *
     * @param  string $text Cyrilic string to convert
     * @return string       Latin string with cut chars
     */
    public function cirToCutLat($text = '')
    {
        return strtr($text, array_merge($this->replacePairs, $this->cutReplacePairs));
    }

    /**
     * Converts latin string to latin with cut chars
     *
     * @param  string $text Latin string to convert
     * @return string       Latin string with cut chars
     */
    public function latToCutLat($text = '')
    {
        return strtr($text, $this->cutLatinChars);
    }
}
