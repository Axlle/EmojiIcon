<?php

namespace EmojiIcon;

class EmojiIcon
{
    /**
     * Emoji image icon uri.
     * 
     * @var string
     */
    protected static $emoji_uri = 'https://raw.githubusercontent.com/iamcal/emoji-data/master/img-apple-160/{emoji}.png';

    /**
     * Icon sizes for universal applications.
     * 
     * @link https://developer.apple.com/library/content/qa/qa1686/_index.html.
     * 
     * @var array
     */
    protected $sizes = [
        'AppIcon-20@2x' => 20 * 2,
        'AppIcon-20@3x' => 20 * 3,
        'AppIcon-29@2x' => 29 * 2,
        'AppIcon-29@3x' => 29 * 3,
        'AppIcon-40@2x' => 40 * 2,
        'AppIcon-40@3x' => 40 * 3,
        'AppIcon-60@2x' => 60 * 2,
        'AppIcon-60@3x' => 60 * 3,
        'AppIcon-20~ipad' => 20,
        'AppIcon-20@2x~ipad' => 20 * 2,
        'AppIcon-29~ipad' => 29,
        'AppIcon-29@2x~ipad' => 29 * 2,
        'AppIcon-40~ipad' => 40,
        'AppIcon-40@2x~ipad' => 40 * 2,
        'AppIcon-76~ipad' => 76,
        'AppIcon-76@2x~ipad' => 76 * 2,
        'AppIcon-83.5@2x~ipad' => 83.5 * 2,
        'AppIcon-256' => 256,
    ];

    /**
     * Starting icon size.
     * 
     * @var int
     */
    protected $icon_size;

    /**
     * Size of emoji.
     *
     * @var int
     */
    protected $emoji_size;

    /**
     * Emoji in UTF-8 encoding.
     * 
     * @var string
     */
    protected $emoji_utf8;

    /**
     * Emoji unicode codepoint value.
     * 
     * @var string
     */
    protected $emoji_unicode;

    /**
     * Starting gradient color RGB seperated.
     * 
     * @var array
     */
    protected $color_start = [0, 0, 0];

    /**
     * Finishing gradient color RGB seperated.
     * 
     * @var array
     */
    protected $color_finish = [0, 0, 0];

    /**
     * Get current working directory.
     * 
     * @var string
     */
    protected $output_directory = __DIR__;

    /**
     * Calcuate icon sizing.
     */
    public function __construct()
    {
        $this->icon_size = max($this->sizes);
        $this->emoji_size = floor($this->icon_size / 1.6180);
    }

    /**
     * Set Emoji value.
     * 
     * @param string $emoji UTF-8 encoded Emoji
     */
    public function setEmoji($emoji)
    {
        $this->emoji_utf8 = $emoji;

        $unicode = self::utf8ToUnicode($this->emoji_utf8);
        $this->emoji_unicode = strtolower(str_replace('\u', '', dechex($unicode)));
    }

    /**
     * Set gradient properties.
     *
     * @param string $a Starting hex color
     * @param string $b Finishing hex color
     */
    public function setGradient($start, $finish)
    {
        $this->color_start = self::hexToRgb($start);
        $this->color_finish = self::hexToRgb($finish);
    }

    /**
     * Set output directory path.
     * 
     * @param string $directory Output directory path
     */
    public function setDirectory($directory)
    {
        $this->output_directory = $directory;
    }

    /**
     * Generate icon files.
     */
    public function generate()
    {
        if (!isset($this->emoji_utf8)) {
            echo self::outputToCli('No Emoji set', 'ERROR');

            exit;
        }

        if (!is_writable($this->output_directory)) {
            echo self::outputToCli('Output directory "' . $this->output_directory . '" is not wriable', 'ERROR');

            exit;
        }

        echo self::outputToCli('Generating icon from: ' . $this->emoji_utf8, 'NOTE');

        $emoji_data = self::getEmojiData($this->emoji_unicode);

        $image_base = imagecreatetruecolor($this->icon_size, $this->icon_size);
        $image_emoji = self::createEmojiImage($emoji_data);

        $image_base = $this->gradientRender(
            $image_base, $this->icon_size, $this->icon_size,
            $this->color_start, $this->color_finish
        );

        imagecopyresampled($image_emoji, $image_emoji, 0, 0, 0, 0,
            $this->emoji_size, $this->emoji_size, 160, 160);

        $dst_x = (($this->icon_size - $this->emoji_size) / 2);
        $dst_y = (($this->icon_size - $this->emoji_size) / 2);

        imagecopy($image_base, $image_emoji,
            $dst_x, $dst_y, 0, 0, $this->emoji_size, $this->emoji_size);

        foreach ($this->sizes as $name => $size) {
            echo self::outputToCli('* ' . $name, 'NOTE');

            $icon = $this->createIcon($image_base, $size);

            imagepng($icon, $this->output_directory.'/'.$name.'.png');
            chmod($this->output_directory.'/'.$name.'.png', 0755);

            imagedestroy($icon);
        }

        echo self::outputToCli('Complete!', 'SUCCESS');
        echo self::outputToCli('Output directory: ' . $this->output_directory, 'NOTE');

        shell_exec('open ' . $this->output_directory);

        imagedestroy($image_base);
    }

    /**
     * Create icon to specified size.
     *
     * @param resource $image_base
     * @param int      $size
     *
     * @return resource Newly resize icon
     */
    protected function createIcon($image_base, $size)
    {
        $icon = imagecreatetruecolor($size, $size);

        imagecopyresampled($icon, $image_base, 0, 0, 0, 0,
            $size, $size, $this->icon_size, $this->icon_size);

        return $icon;
    }

    /**
     * Convert Emoji UTF-8 value to unicode codepoint.
     *
     * @param string $v UTF-8 encoded Emoji
     *
     * @return string Emoji unicode codepoint value
     */
    protected static function utf8ToUnicode($c)
    {
        $ord0 = ord($c{0});
        if ($ord0 >= 0 && $ord0 <= 127) {
            return $ord0;
        }
        $ord1 = ord($c{1});
        if ($ord0 >= 192 && $ord0 <= 223) {
            return ($ord0 - 192) * 64 + ($ord1 - 128);
        }
        $ord2 = ord($c{2});
        if ($ord0 >= 224 && $ord0 <= 239) {
            return ($ord0 - 224) * 4096 + ($ord1 - 128) * 64 + ($ord2 - 128);
        }
        $ord3 = ord($c{3});
        if ($ord0 >= 240 && $ord0 <= 247) {
            return ($ord0 - 240) * 262144 + ($ord1 - 128) * 4096 + ($ord2 - 128) * 64 + ($ord3 - 128);
        }

        return false;
    }

    protected static function hexToRgb($hex)
    {
        $hex = str_replace('#', '', $hex);

        if (strlen($hex) == 3) {
            $r = hexdec(substr($hex, 0, 1).substr($hex, 0, 1));
            $g = hexdec(substr($hex, 1, 1).substr($hex, 1, 1));
            $b = hexdec(substr($hex, 2, 1).substr($hex, 2, 1));
        } else {
            $r = hexdec(substr($hex, 0, 2));
            $g = hexdec(substr($hex, 2, 2));
            $b = hexdec(substr($hex, 4, 2));
        }

        $rgb = [$r, $g, $b];

        return $rgb;
    }

    /**
     * Get raw Emoji data from source.
     * 
     * @param string $emoji Emoji unicode codepoint value
     *
     * @return string Raw Emoji image data
     */
    protected static function getEmojiData($emoji)
    {
        $uri = str_replace('{emoji}', $emoji, self::$emoji_uri);

        $emoji_data = @file_get_contents($uri);

        if ($emoji_data === false) {
            echo self::outputToCli('Emoji data not found', 'ERROR');

            exit;
        }

        return $emoji_data;
    }

    /**
     * Create Emoji resource from raw image data.
     * 
     * @param string $emoji_data Raw Emoji image data
     *
     * @return resource Image resource
     */
    protected static function createEmojiImage($emoji_data)
    {
        $image_emoji = imagecreatefromstring($emoji_data);

        imagealphablending($image_emoji, false);
        imagesavealpha($image_emoji, true);

        return $image_emoji;
    }

    /**
     * Generate a gradient from two colors.
     * 
     * @param resource $image
     * @param int      $image_width  Image height
     * @param int      $image_height Image width
     * @param array    $start        Starting color as RGB array
     * @param array    $finish       Finishing color as RGB array
     * 
     * @return resource
     */
    public static function gradientRender($image, $image_width, $image_height, $start, $finish)
    {
        // render gradient step by step
        for ($i = 0; $i < $image_height; ++$i) {
            // get each color component for this step
            $color_r = floor($i * ($finish[0] - $start[0]) / $image_height) + $start[0];
            $color_g = floor($i * ($finish[1] - $start[1]) / $image_height) + $start[1];
            $color_b = floor($i * ($finish[2] - $start[2]) / $image_height) + $start[2];

            // create this color
            $color = imagecolorallocate($image, $color_r, $color_g, $color_b);

            // draw a line using this color
            imageline($image, 0, $i, $image_width, $i, $color);
        }

        return $image;
    }

    public static function outputToCli($text, $status)
    {
        $output = '';
        switch($status) {
            case 'SUCCESS':
                $output = '[32m';
                break;
            case 'ERROR':
                $output = '[31m';
                break;
            case 'WARNING':
                $output = '[33m';
                break;
            case 'NOTE':
                $output = '[34m';
                break;
        }

        return chr(27) . $output . $text . chr(27) . '[0m' . PHP_EOL;
    }
}
