<?php
/*
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Rasmus Fuhse <fuhse@data-quest.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP-Plugins
 * @since       2.1
 */

class CSVImportProcessor_serienbriefe {

    static public function CSV2Array($content, $delim = ';', $encl = '"', $optional = 1) {
		if ($content[strlen($content)-1]!="\r" && $content[strlen($content)-1]!="\n")
		$content .= "\r\n";

		$reg = '/(('.$encl.')'.($optional?'?(?(2)':'(').
		'[^'.$encl.']*'.$encl.'|[^'.$delim.'\r\n]*))('.$delim.
		'|[\r\n]+)/smi';

		preg_match_all($reg, $content, $treffer);
		$linecount = 0;

		for ($i = 0; $i < count($treffer[3]);$i++) {
			$liste[$linecount][] = str_replace($encl.$encl, $encl, trim($treffer[1][$i],$encl));
			if ($treffer[3][$i] != $delim) $linecount++;
		}
		return $liste;
	}

    static public function getCSVDataFromFile($file_path) {
        return self::CSV2Array(file_get_contents($file_path));
    }

    static public function reduce_diakritika_from_iso88591($text) {
		$text = str_replace(array("�","�","�","�","�","�","�"), array('ae','Ae','oe','Oe','ue','Ue','ss'), $text);
		$text = str_replace(array('�','�','�','�','�','�'), 'A' , $text);
		$text = str_replace(array('�','�','�','�','�','�'), 'a' , $text);
		$text = str_replace(array('�','�','�','�'), 'E' , $text);
		$text = str_replace(array('�','�','�','�'), 'e' , $text);
		$text = str_replace(array('�','�','�','�'), 'I' , $text);
		$text = str_replace(array('�','�','�','�'), 'i' , $text);
		$text = str_replace(array('�','�','�','�','�'), 'O' , $text);
		$text = str_replace(array('�','�','�','�','�'), 'o' , $text);
		$text = str_replace(array('�','�','�'), 'U' , $text);
		$text = str_replace(array('�','�','�'), 'u' , $text);
		$text = str_replace(array('�','�','�','�','�','�','�','�'), array('C','c','D','N','Y','n','y','y') , $text);
		return $text;
	}

}
