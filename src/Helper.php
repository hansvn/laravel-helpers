<?php

namespace Hansvn\Helper;

use DateTime;

class Helper {
    
    /**
     * function to format a date
     *
     * @param date
     * @param date_format
     **/
    public static function formatDate($date, $format = null) {
        if(!$date && config('helper.always_display_date')) {
            $date = DateTime::createFromFormat('Y-m-d H:i:s', '0001-01-01 00:00:00');
        } elseif(!$date) {
            if(config('helper.default_zero_value')) return config('helper.default_zero_value');
            return "";
        }

        if( !($date instanceof DateTimeInterface) ) {
            $date = new DateTime($date);
        }

        if( !$format ) {
            $format = config('helper.default_date_format');
        }

        return $date->format($format);
    }

    /**
     * check if a given string is json
     *
     * @param string $string
     * @return boolean
     **/
    public static function isJson($string) {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }

    /**
     * cast object to class
     *
     * @param object $obj
     * @param string $to_class
     * @return boolean
     **/
    public static function cast($obj, $to_class) {
        if(class_exists($to_class)) {
            $obj_in = serialize($obj);
            $obj_out = 'O:' . strlen($to_class) . ':"' . $to_class . '":' . substr($obj_in, $obj_in[2] + 7);
            return unserialize($obj_out);
        }
        else
            return false;
    }

    /**
     * base64 encode files
     *
     * @param string $directory
     * @param string $file
     * @return base64 string
     **/

    public static function toBase64($directory, $file = null, $with_mime = false) {
        if($file == null) $absolutePath = $directory;
        else $absolutePath = $directory.DIRECTORY_SEPARATOR.$file;

        //remove possible double directory separators
        $absolutePath = str_replace(DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR, $absolutePath);
        
        if(file_exists($absolutePath)) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE); // return mime type ala mimetype extension
            $mime = finfo_file($finfo, $absolutePath);
            finfo_close($finfo);

            if($with_mime) {
                return array(
                    'mime' => $mime,
                    'data' => "data:$mime;base64,".base64_encode(file_get_contents($absolutePath))
                );
            }

            return "data:$mime;base64,".base64_encode(file_get_contents($absolutePath));
        }
        else return null;
    }

    /**
     * function to calculate date difference
     *
     * @param date
     * @param time since date
     **/
    public static function timeSince($date, $now = null) {
        if(!$now) $now = new DateTime;
        
        if(!$date && config('helper.always_display_date')) {
            $date = DateTime::createFromFormat('Y-m-d H:i:s', '0001-01-01 00:00:00');
        } elseif(!$date) {
            return "";
        }

        if( !($date instanceof DateTime) )
            $date = new DateTime($date);
        
        $interval = $now->diff($date, true);

        if($interval->y > 0) {
            return $interval->format('%y years');
        } else if($interval->m > 0) {
            return $interval->format('%m months');
        } else if($interval->d > 0) {
            return $interval->format('%d days');
        } else if($interval->h > 0) {
            return $interval->format('%h hours');
        } else if($interval->i > 0) {
            return $interval->format('%i minutes');
        } else {
            return $interval->format('%s seconds');
        }
    }
    
    /**
     * cut a text to specified length and add '...' at the and of the string
     */
    public static function truncate($text, $length = 100, $options = array()) {
        $default = array(
            'ending' => '...', 'exact' => true, 'html' => false
        );
        $options = array_merge($default, $options);
        extract($options);

        if ($html) {
            if (mb_strlen(preg_replace('/<.*?>/', '', $text)) <= $length) {
                return $text;
            }
            $totalLength = mb_strlen(strip_tags($ending));
            $openTags = array();
            $truncate = '';

            preg_match_all('/(<\/?([\w+]+)[^>]*>)?([^<>]*)/', $text, $tags, PREG_SET_ORDER);
            foreach ($tags as $tag) {
                if (!preg_match('/img|br|input|hr|area|base|basefont|col|frame|isindex|link|meta|param/s', $tag[2])) {
                    if (preg_match('/<[\w]+[^>]*>/s', $tag[0])) {
                        array_unshift($openTags, $tag[2]);
                    } else if (preg_match('/<\/([\w]+)[^>]*>/s', $tag[0], $closeTag)) {
                        $pos = array_search($closeTag[1], $openTags);
                        if ($pos !== false) {
                            array_splice($openTags, $pos, 1);
                        }
                    }
                }
                $truncate .= $tag[1];

                $contentLength = mb_strlen(preg_replace('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|&#x[0-9a-f]{1,6};/i', ' ', $tag[3]));
                if ($contentLength + $totalLength > $length) {
                    $left = $length - $totalLength;
                    $entitiesLength = 0;
                    if (preg_match_all('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|&#x[0-9a-f]{1,6};/i', $tag[3], $entities, PREG_OFFSET_CAPTURE)) {
                        foreach ($entities[0] as $entity) {
                            if ($entity[1] + 1 - $entitiesLength <= $left) {
                                $left--;
                                $entitiesLength += mb_strlen($entity[0]);
                            } else {
                                break;
                            }
                        }
                    }

                    $truncate .= mb_substr($tag[3], 0 , $left + $entitiesLength);
                    break;
                } else {
                    $truncate .= $tag[3];
                    $totalLength += $contentLength;
                }
                if ($totalLength >= $length) {
                    break;
                }
            }
        } else {
            if (mb_strlen($text) <= $length) {
                return $text;
            } else {
                $truncate = mb_substr($text, 0, $length - mb_strlen($ending));
            }
        }
        if (!$exact) {
            $spacepos = mb_strrpos($truncate, ' ');
            if (isset($spacepos)) {
                if ($html) {
                    $bits = mb_substr($truncate, $spacepos);
                    preg_match_all('/<\/([a-z]+)>/', $bits, $droppedTags, PREG_SET_ORDER);
                    if (!empty($droppedTags)) {
                        foreach ($droppedTags as $closingTag) {
                            if (!in_array($closingTag[1], $openTags)) {
                                array_unshift($openTags, $closingTag[1]);
                            }
                        }
                    }
                }
                $truncate = mb_substr($truncate, 0, $spacepos);
            }
        }
        $truncate .= $ending;

        if ($html) {
            foreach ($openTags as $tag) {
                $truncate .= '</'.$tag.'>';
            }
        }

        return $truncate;
    }

    /**
     * Get the mime type from file extension
     *
     * @param string $extenstion
     * @param string $default
     * @return string
     **/

    public static function mime($extension, $default = 'application/octet-stream') {
        $mimes = array('hqx' => 'application/mac-binhex40','cpt' => 'application/mac-compactpro','csv' => array('text/x-comma-separated-values', 
            'text/comma-separated-values', 'application/octet-stream'),'bin' => 'application/macbinary','dms' => 'application/octet-stream',
            'lha' => 'application/octet-stream','lzh' => 'application/octet-stream','exe' => array('application/octet-stream', 'application/x-msdownload'),
            'class'=> 'application/octet-stream','psd' => 'application/x-photoshop','so'  => 'application/octet-stream','sea' => 'application/octet-stream',
            'dll' => 'application/octet-stream','oda' => 'application/oda','pdf' => array('application/pdf', 'application/x-download'),'ai'  => 'application/postscript',
            'eps' => 'application/postscript','ps'  => 'application/postscript','smi' => 'application/smil','smil'=> 'application/smil','mif' => 'application/vnd.mif',
            'xls' => array('application/excel', 'application/vnd.ms-excel', 'application/msexcel'),'ppt' => array('application/powerpoint', 'application/vnd.ms-powerpoint'),
            'wbxml'=> 'application/wbxml','wmlc'=> 'application/wmlc','dcr' => 'application/x-director','dir' => 'application/x-director','dxr' => 'application/x-director',
            'dvi' => 'application/x-dvi','gtar'=> 'application/x-gtar','gz'  => 'application/x-gzip','php' => array('application/x-httpd-php', 'text/x-php'),
            'php4'=> 'application/x-httpd-php','php3'=> 'application/x-httpd-php','phtml'=> 'application/x-httpd-php','phps'=> 'application/x-httpd-php-source',
            'js'  => 'application/x-javascript','swf' => 'application/x-shockwave-flash','sit' => 'application/x-stuffit','tar' => 'application/x-tar',
            'tgz' => array('application/x-tar', 'application/x-gzip-compressed'),'xhtml'=> 'application/xhtml+xml','xht' => 'application/xhtml+xml',
            'zip' => array('application/x-zip', 'application/zip', 'application/x-zip-compressed'),'mid' => 'audio/midi','midi'=> 'audio/midi','mpga'=> 'audio/mpeg',
            'mp2' => 'audio/mpeg','mp3' => array('audio/mpeg', 'audio/mpg', 'audio/mpeg3', 'audio/mp3'),'aif' => 'audio/x-aiff','aiff'=> 'audio/x-aiff',
            'aifc'=> 'audio/x-aiff','ram' => 'audio/x-pn-realaudio','rm'  => 'audio/x-pn-realaudio','rpm' => 'audio/x-pn-realaudio-plugin','ra'  => 'audio/x-realaudio',
            'rv'  => 'video/vnd.rn-realvideo','wav' => 'audio/x-wav','bmp' => 'image/bmp','gif' => 'image/gif','jpeg'=> array('image/jpeg', 'image/pjpeg'),
            'jpg' => array('image/jpeg', 'image/pjpeg'),'jpe' => array('image/jpeg', 'image/pjpeg'),'png' => 'image/png','tiff'=> 'image/tiff','tif' => 'image/tiff',
            'css' => 'text/css','html'=> 'text/html','htm' => 'text/html','shtml'=> 'text/html','txt' => 'text/plain','text'=> 'text/plain',
            'log' => array('text/plain', 'text/x-log'),'rtx' => 'text/richtext','rtf' => 'text/rtf','xml' => 'text/xml','xsl' => 'text/xml','mpeg'=> 'video/mpeg',
            'mpg' => 'video/mpeg','mpe' => 'video/mpeg','qt'  => 'video/quicktime','mov' => 'video/quicktime','avi' => 'video/x-msvideo','movie'=> 'video/x-sgi-movie',
            'doc' => 'application/msword','docx'=> 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xlsx'=> 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet','word'=> array('application/msword', 'application/octet-stream'),
            'xl'  => 'application/excel','eml' => 'message/rfc822','json'=> array('application/json', 'text/json'), );

        $extension = strtolower($extension);

        if ( ! array_key_exists($extension, $mimes)) return $default;
        return (is_array($mimes[$extension])) ? $mimes[$extension][0] : $mimes[$extension];
    }

    /**
     * Check if a folder exists and create if not exists
     *
     * @param string $folder
     * @param string $persmissions
     * @return boolean
     **/

    public static function checkForFolder($folder, $permissions = 0775) {
        if (!file_exists($folder)) {
            if( mkdir($folder, $permissions, true) )
                return true;
            else
                return false;
        }
        else {
            return true;
        }
    }
    
    /**
     * Check if a string is serialized
     *
     * @param string $string
     * @return boolean
     **/

    function is_serialized($string) {
        $test = @unserialize($string);
        if ($string === 'b:0;' || $test !== false) {
            //this is indead serialized string
            return true;
        }
            
        return false;
    }
}