<?php
/**
 * Defines the language class used for translating system strings by using
 * po files for different languages.
 * 
 * @author Jefferson González
 * @license MIT
 * @link http://github.com/wxphp/peg Source code.
*/

namespace Cms;

/**
 * For translating strings into other languages.
 */
class Language
{
	/**
	 * One of the language codes @see \Cms\Enumerations\LanguageCode
	 * @var string
	 */
	private $language;
	
	/**
	 * Directory that holds po files for each language.
	 * @var string 
	 */
	private $directory;
	
	/**
	 * Stores the translation strings parsed from po files.
	 * @var array 
	 */
	private $translations;
	
	/**
	 * Initialize the language class for translating strings.
	 * @param string $directory Path to directory that holds po files.
	 * @param string $language The language code @see \Cms\Enumerations\LanguageCode
	 * @throws Exception If language directory doesn't exists.
	 */
	public function __construct($directory, $language=null)
	{
		if(!file_exists($directory))
			throw new \Exception(t('Languages directory not found.'));
		
		$this->translations = null;
		
		$this->directory = $directory;
		
		$this->SetLanguage($language);
	}
	
	/**
	 * Current language used for translation output.
	 * @return string Language code @see \Cms\Enumerations\LanguageCode
	 */
	public function GetLanguage()
	{
		return $this->language;
	}
	
	/**
	 * Gets the current system language.
	 * @return string Language code @see \Cms\Enumerations\LanguageCode
	 */
	public function GetSystemLanguage()
	{
		//TODO: Implement also for microsoft windows
		$language_parts = explode(':', $_SERVER['LANGUAGE']);
		
		return $language_parts[0];
	}
	
	/**
	 * Assings the language for translation output. If no language is given
	 * the system one is assigned.
	 * @param string $language Language code @see \Cms\Enumerations\LanguageCode
	 */
	public function SetLanguage($language=null)
	{
		$this->translations = null;
		
		if(!$language)
		{
			$this->language = $this->GetSystemLanguage();
		}
		else
		{
			$this->language = $language;
		}
		
		// If language files does not exists and a sublanguage was used
		// like en_US then use 'en' to see if it is available.
		if(!file_exists($this->directory . '/' . $this->language))
		{
			$language_parts = explode('_', $this->language);

			if(count($language_parts) > 1)
			{
				$this->language = $language_parts[0];
			}
		}
	}
	
	/**
	 * Translates a given text to the currently language set.
	 * @param string $text String to translate.
	 * @return string Translated text or original if no translation found.
	 */
	public function Translate($text)
	{
		$text = trim($text);

		$translation = $text;

		if(!$this->translations)
		{
			$this->translations = $this->PoParser($this->directory . '/' . $this->language . '.po');
		}

		if($text != '')
		{
			if(isset($this->translations[$text]))
			{
				$available_translation = $this->translations[$text];

				if($available_translation != '')
				{
					$translation = $available_translation;
				}
			}
		}

		return $translation;
	}

	/**
	 * Parses a .po file generated by gettext tools.
	 * @param string $file The path of the po translations file.
	 * @return array In the format array["original text"] = "translation"
	 */
	public function PoParser($file)
	{
		if(!file_exists($file))
		{
			return false;
		}

		$file_rows = file($file);

		$original_string = '';
		$translations = array();

		$found_original = false;

		foreach($file_rows as $row)
		{
			if(!$found_original)
			{
				if(substr(trim($row),0,6) == 'msgid ')
				{
					$found_original = true;
					$string = str_replace('msgid ', '', trim($row));

					$pattern = "/(\")(.*)(\")/";
					$replace = "\$2";
					$string = preg_replace($pattern, $replace, $string);
					$string = str_replace(
						array("\\t", "\\n", "\\r", "\\0", "\\v", "\\f", "\\\\", "\\\""), 
						array("\t", "\n", "\r", "\0", "\v", "\f", "\\", "\""), 
						$string
					);

					$original_string = $string;
				}
			}
			else
			{
				if(substr(trim($row),0,7) == 'msgstr ')
				{
					$found_original = false;
					$string = str_replace('msgstr ', '', trim($row));

					$pattern = "/(\")(.*)(\")/";
					$replace = "\$2";
					$string = preg_replace($pattern, $replace, $string);
					$string = str_replace(
						array("\\t", "\\n", "\\r", "\\0", "\\v", "\\f", "\\\\", "\\\""), 
						array("\t", "\n", "\r", "\0", "\v", "\f", "\\", "\""), 
						$string
					);

					$translations[$original_string] = $string;
				}
			}
		}

		unset($file_rows);

		return $translations;
	}
    
    /**
     * Sets the language to the best match of the user browser language.
     */
    public function SetLanguageAsBrowser()
    {
        $this->translations = null;
        
        if(isset($_SERVER['HTTP_ACCEPT_LANGUAGE']))
        {
            $user_languages = explode(',', str_replace(' ', '', strtolower($_SERVER['HTTP_ACCEPT_LANGUAGE'])));

            foreach($user_languages as $user_language)
            {
                $language_code_array = explode(';', $user_language);

                $language_code = explode('-', $language_code_array[0]);

                if(count($language_code) > 1)
                {
                    $glue = implode('-', $language_code);
                    if($this->LanguageFileExists($glue) || $glue == 'en')
                    {
                        $this->language = $glue;
                        return;
                    }
                    elseif($this->LanguageFileExists($language_code[0]) || $language_code[0] == 'en')
                    {
                        $this->language = $language_code[0];
                        return;
                    }
                }
                elseif($this->LanguageFileExists($language_code[0]) || $language_code[0] == 'en')
                {
                    $this->language = $language_code[0];
                    return;
                }
            }
        }

        $this->language = 'en';
    }
    
    /**
     * Checks if a given language code translations exists.
     * @param string $language
     * @return boolean
     */
    private function LanguageFileExists($language)
    {
        if(file_exists($this->directory . '/' . $language . '.po'))
            return true;
        
        return false;
    }
    
    /**
     * Get array of available language files on the given path.
     * @param string $path Directory that contains po files.
     * @return array In format array('Language Label' => 'language_code')
     */
    public static function GetAvailable($path)
    {
        $path = rtrim($path, '/\\');
        
        $dir_handle = opendir($path);
        $languages = array();

        while (($language_file = readdir($dir_handle)) !== false)
        {
            if(!is_file($path . '/' . $language_file))
                continue;
            
            if($language_file == 'default.po')
                continue;

            $language = explode('.po', $language_file);
            
            if($language[0] != 'README')
                $languages[
                    Enumerations\LanguageCode::GetLabel($language[0])
                ] = $language[0];
        }

        return $languages;
    }
}

?>
