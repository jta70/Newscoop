<?php

class CampTemplateCache
{
    /**
     * Loads the handler specified by the given name.
     * @param $p_handlerName
     * @return object
     */
    public static function factory($p_handlerName = null, $p_path = null)
    {
        static $handlers;

        if (!$p_handlerName) {
            $p_handlerName = SystemPref::Get('TemplateCacheHandler');
        }
        if (!empty($handlers[$p_handlerName])) {
            return $handlers[$p_handlerName];
        }
        if (is_null($p_path)) {
            $path = dirname(__FILE__) . DIR_SEP. 'cache';
        } else {
            $path = $p_path;
        }
        $filePath = "$path/TemplateCacheHandler_$p_handlerName.php";
        if (file_exists($filePath)) {
            require_once($filePath);
            $className = "TemplateCacheHandler_$p_handlerName";
            if (class_exists($className)) {
                $handlerObj = new $className;
                if ($handlerObj->isSupported()) {
                    $handlers[$p_handlerName] = $handlerObj;
                    return $handlerObj;
                }
            }
        }
        return null;
    }

    /**
     * Returns an array of available handlers containing
     * handler name -> info pairs.
     * @param $p_path
     * @return array
     */
    public static function availableHandlers($p_path = null)
    {
        if (is_null($p_path)) {
            $path = dirname(__FILE__) . DIR_SEP. 'cache';
        } else {
            $path = $p_path;
        }

        require_once(dirname(dirname(__FILE__)).'/include/pear/File/Find.php');
        $includeFiles = File_Find::search('/^TemplateCacheHandler_[^.]*\.php$/', $path, 'perl', false);
        $handlers = array();
        foreach ($includeFiles as $includeFile) {
            if (preg_match('/TemplateCacheHandler_([^.]+)\.php/', $includeFile, $matches) == 0) {
                continue;
            }
            require_once $includeFile;
            $handlerName = $matches[1];
            $className = "TemplateCacheHandler_$handlerName";
            if (class_exists($className)) {
                $cacheHandler = new $className;
                $handlers[$handlerName] = array(
                    'is_supported'=>$cacheHandler->isSupported(),
                    'file'=>"$path/TemplateCacheHandler_$handlerName.php",
                    'description'=>$cacheHandler->description());
            }
        }
        return $handlers;
    }
}

?>