<?php
namespace App\Utils;

class RemoveSpecialCharactersInString {
    public static function remove(string $string) {
        $regex_all_emojis = '/(?:[\x{1F600}-\x{1F64F}]|[\x{1F300}-\x{1F5FF}]|[\x{1F680}-\x{1F6FF}]|[\x{2600}-\x{26FF}]| [\x{2700}-\x{27BF}]|[\x{1F900}-\x{1F9FF}]|[\x{1FA00}-\x{1FA6F}]|[\x{1FA70}-\x{1FAFF}]|[\x{20D0}-\x{20FF}]|[\x{FE00}-\x{FE0FF}])/u';
        return preg_replace($regex_all_emojis, '', $string);
    }
}
