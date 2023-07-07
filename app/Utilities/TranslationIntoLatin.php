<?php
namespace App\Utilities;

class TranslationIntoLatin
{
    static function translate($value){
        $value = (string) $value;
        $value = trim($value);
        $value = function_exists('mb_strtolower') ? mb_strtolower($value) : strtolower($value);
        $value = strtr($value, array('а'=>'a','б'=>'b','в'=>'v','г'=>'g','д'=>'d','е'=>'e','ё'=>'e','ж'=>'j','з'=>'z','и'=>'i','й'=>'y','к'=>'k','л'=>'l','м'=>'m','н'=>'n','о'=>'o','п'=>'p','р'=>'r','с'=>'s','т'=>'t','у'=>'u','ф'=>'f','х'=>'h','ц'=>'c','ч'=>'ch','ш'=>'sh','щ'=>'shch','ы'=>'y','э'=>'e','ю'=>'yu','я'=>'ya','ъ'=>'','ь'=>''));

        return $value;
    }

//    public function unTranslate($value){
//
//        $value = (string) $value;
//        $value = trim($value);
//        $value = function_exists('mb_strtolower') ? mb_strtolower($value) : strtolower($value);
//        $value = strtr($value, array("a" => "а","b" => "б","v" => "в","g" => "г","d" => "д","e" => "э","j" => "ж","z" => "з","i" => "и","y" => "ы","k" => "к","l" => "л","m" => "м","n" => "н","o" => "о","p" => "п","r" => "р","s" => "с","t" => "т","u" => "у","f" => "ф","h" => "х","c" => "ц","ch" => "ч","sh" => "ш","shch" => "щ","yu" => "ю","ya" => "я"));
//
//        return $value;
//    }

}