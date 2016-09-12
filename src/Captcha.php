<?php
/*
 * mobiCMS Content Management System (http://mobicms.net)
 *
 * For copyright and license information, please see the LICENSE.md
 * Installing the system or redistributions of files must retain the above copyright notice.
 *
 * @link        http://mobicms.net mobiCMS Project
 * @copyright   Copyright (C) mobiCMS Community
 * @license     LICENSE.md (see attached file)
 */

namespace Mobicms\Captcha;

/**
 * Class Captcha
 *
 * @package Mobicms\Captcha
 * @author  Oleg (AlkatraZ) Kasyanov <dev@mobicms.net>
 * @version v.2.2.0 2015-08-13
 */
class Captcha
{
    /**
     * @var int Image Width
     */
    public $width = 160;

    /**
     * @var int Image Height
     */
    public $height = 50;

    /**
     * @var int Default font size
     */
    public $fontSize = 24;

    /**
     * @var array Individual sizes of fonts (if not present, use default)
     */
    public $customFonts = [
        '3dlet.ttf'          => ['size' => 32, 'case' => 1],
        'baby_blocks.ttf'    => ['size' => 16, 'case' => 0],
        'betsy_flanagan.ttf' => ['size' => 28, 'case' => 0],
        'granps.ttf'         => ['size' => 26, 'case' => 2],
        'karmaticarcade.ttf' => ['size' => 20, 'case' => 0],
        'tonight.ttf'        => ['size' => 28, 'case' => 0],
    ];

    /**
     * @var int The minimum length of Captcha
     */
    public $lenghtMin = 3;

    /**
     * @var int The maximum length of Captcha
     */
    public $lenghtMax = 5;

    /**
     * @var string Symbols used in Captcha
     */
    public $letters = '23456789ABCDEGHKMNPQSUVXYZabcdeghkmnpqsuvxyz';

    /**
     * Captcha code generation
     *
     * @return string
     */
    public function generateCode()
    {
        $lenght = mt_rand($this->lenghtMin, $this->lenghtMax);

        do {
            $code = substr(str_shuffle(str_repeat($this->letters, 3)), 0, $lenght);
        } while (preg_match('/cp|cb|ck|c6|c9|rn|rm|mm|co|do|cl|db|qp|qb|dp|ww/', $code));

        return $code;
    }

    /**
     * Captcha image generation
     *
     * @param $string
     * @return string
     */
    public function generateImage($string)
    {
        $font = $this->chooseFont();
        $captcha = $this->prepareString($string, $font);

        $image = imagecreatetruecolor($this->width, $this->height);
        imagesavealpha($image, true);
        imagefill($image, 0, 0, imagecolorallocatealpha($image, 0, 0, 0, 127));
        $this->drawText($image, $captcha, $font);

        ob_start();
        imagepng($image);
        imagedestroy($image);

        return 'data:image/png;base64,' . base64_encode(ob_get_clean());
    }

    /**
     * Drawing the text on the image
     *
     * @param resource $image
     * @param array    $captcha
     * @param string   $font
     */
    private function drawText(&$image, array $captcha, $font)
    {
        $len = count($captcha);

        for ($i = 0; $i < $len; $i++) {
            $xPos = ($this->width - $this->fontSize) / $len * $i + ($this->fontSize / 2);
            $xPos = mt_rand($xPos, $xPos + 5);
            $yPos = $this->height - (($this->height - $this->fontSize) / 2);
            $capcolor = imagecolorallocate($image, rand(0, 150), rand(0, 150), rand(0, 150));
            $capangle = rand(-25, 25);
            imagettftext($image, $this->fontSize, $capangle, $xPos, $yPos, $capcolor, $font, $captcha[$i]);
        }
    }

    /**
     * Choosing a random font from the list of available
     *
     * @return string
     */
    private function chooseFont()
    {
        $dir = __DIR__ . DS . 'fonts' . DS;
        $fontsList = glob($dir . '*.ttf');
        $font = basename($fontsList[mt_rand(0, count($fontsList) - 1)]);

        return $dir . $font;
    }

    /**
     * Set font size
     *
     * @param string $string
     * @param string $font
     * @return array
     */
    private function prepareString($string, $font)
    {
        $font = basename($font);

        if (isset($this->customFonts[$font])) {
            $args = $this->customFonts[$font];
            $this->fontSize = $args['size'];
            $string = $this->setCase($string, $args);
        }

        return str_split($string);
    }

    /**
     * Set font case
     *
     * @param string $string
     * @param array  $args
     * @return string
     */
    private function setCase($string, array $args)
    {
        switch ($args['case']) {
            case 2:
                return strtoupper($string);
            case 1:
                return strtolower($string);
        }

        return $string;
    }
}