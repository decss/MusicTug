<?php
/**
 * MusicTug helper class
 */
class MusicTugHelper
{
    private static $_config     = array();

    private static $_tagFilter  = array(
        m4a     => array('title', 'album', 'artist', 'genre', 'comment', 'date' => 'year', 'track' => 'tracknum', 'tempo' => 'bpm'),
        mp3     => array('title', 'album', 'artist', 'genre', 'comment', 'date',           'track',               'tempo' => 'TBPM'),
    );
    private static $_extFilter  = array(
        track   => array('m4a', 'mp3'),
        artwork => array('jpg', 'png', 'bmp', 'gif'),
    );


    /**
     * Open, check and return and return config
     * @return array MusicTug configuration array
     */
    static function getConfig()
    {
        if (is_file(MT_CONFIG_FILE) == false) {
            self::_createConfig();
        }

        require_once MT_CONFIG_FILE;
        
        self::$_config = $mtConfig;
        self::_checkConfig();

        return self::$_config;
    }

    /**
     * Check some config and set default values
     */
    private static function _checkConfig()
    {
        // Chect track ext
        if (self::$_config[trackExt] != null AND !in_array(self::$_config[trackExt], self::$_extFilter[track])) {
            self::$_config[trackExt] = self::$_extFilter[track][0];
        }
        // Chect artwork ext
        if (self::$_config[artworkExt] != null AND !in_array(self::$_config[artworkExt], self::$_extFilter[artwork])) {
            self::$_config[artworkExt] = self::$_extFilter[artwork][0];
        }

        self::$_config[genrePlsStore] = intval(self::$_config[genrePlsStore]);
        self::$_config[moodPlsStore]  = intval(self::$_config[moodPlsStore]);
        self::$_config[tempoPlsStore] = intval(self::$_config[tempoPlsStore]);
    }

    /**
     * Create config file with default values
     */
    private static function _createConfig()
    {
        $configText .= '<?php' . "\r\n";
        $configText .= '$mtConfig[version]             = "20141028";' . "\r\n";
        $configText .= "\r\n";
        $configText .= '// Must be filled:' . "\r\n";
        $configText .= '$mtConfig[lastfmKey]           = "";' . "\r\n";
        $configText .= '$mtConfig[gracenoteClientId]   = "";' . "\r\n";
        $configText .= '$mtConfig[gracenoteUserId]     = "";' . "\r\n";
        $configText .= '$mtConfig[gracenoteHost]       = "https://208.72.242.176/webapi/xml/1.0/";' . "\r\n";
        $configText .= '$mtConfig[curlProxy]           = "";' . "\r\n";
        $configText .= "\r\n";
        $configText .= '$mtConfig[downloadPath]        = "E:\Music\pandora-maintest-3";' . "\r\n";
        $configText .= '$mtConfig[tmpPath]             = ".\_tmp";' . "\r\n";
        $configText .= '$mtConfig[playlistsPath]       = ".\_playlists";' . "\r\n";
        $configText .= '$mtConfig[shell]               = "powershell";' . "\r\n";
        $configText .= '$mtConfig[powershellPath]      = "%SYSTEMROOT%\System32\WindowsPowerShell\v1.0\\\";' . "\r\n";
        $configText .= "\r\n";
        $configText .= "// Tags, Lyrics, Artwork\r\n";
        $configText .= '$mtConfig[embedArtwork]        = true;'  . "\r\n";
        $configText .= '$mtConfig[storeArtwork]        = true;'  . "\r\n";
        $configText .= '$mtConfig[parseLyrics]         = true;'  . "\r\n";
        $configText .= '$mtConfig[embedLyrics]         = true;'  . "\r\n";
        $configText .= '$mtConfig[storeLyrics]         = true;'  . "\r\n";
        $configText .= '$mtConfig[parseTags]           = true;'  . "\r\n";
        $configText .= '$mtConfig[embedTags]           = true;'  . "\r\n";
        $configText .= "\r\n";
        $configText .= "// Extantions, similar limit\r\n";
        $configText .= '$mtConfig[trackExt]            = "m4a";' . "\r\n";
        $configText .= '$mtConfig[artworkExt]          = "jpg";' . "\r\n";
        $configText .= '$mtConfig[similarMin]          = 60;'    . "\r\n";
        $configText .= "\r\n";
        $configText .= "// Playlists\r\n";
        $configText .= '$mtConfig[genrePlsStore]       = 3; // 0..3, 0 - to disable'  . "\r\n";
        $configText .= '$mtConfig[moodPlsStore]        = 3;'  . "\r\n";
        $configText .= '$mtConfig[tempoPlsStore]       = 3;'  . "\r\n";
        $configText .= '$mtConfig[genrePlsPrefix]      = "[G] ";'  . "\r\n";
        $configText .= '$mtConfig[moodPlsPrefix]       = "[M] ";'  . "\r\n";
        $configText .= '$mtConfig[tempoPlsPrefix]      = "[T] ";'  . "\r\n";
        $configText .= '$mtConfig[plsDir_L1]           = "";'  . "\r\n";
        $configText .= '$mtConfig[plsDir_L2]           = "_pls-level-2";'  . "\r\n";
        $configText .= '$mtConfig[plsDir_L3]           = "_pls-level-3";'  . "\r\n";

        $configText .= "\r\n";
        $configText .= "// \r\n";
        $configText .= '$mtConfig[logPath]             = "log/get_lyrics_link.log";'  . "\r\n";

        file_put_contents(MT_CONFIG_FILE, $configText);
    }


    /**
     * Print Json result and exit
     * @param array $jsonAnswer optional data to json encode
     */
    static function printAnswer($jsonAnswer = null)
    {
        if (!$jsonAnswer) {
            $jsonAnswer->error["code"] = 1;
        }

        $jsonAnswer = json_encode($jsonAnswer);

        if ($_GET[callback]) {
            $jsonAnswer = $_GET[callback].'('.$jsonAnswer.')';
        }

        echo $jsonAnswer;
        exit;
    }


    /**
     * Get playlist sysetm path in $_config[playlistsPath] dir
     * @param string $plsId
     * @return string Playlist path
     */
    static function getPlaylistSysPath($plsId)
    {
        $plsSysPath = self::$_config[playlistsPath] . DIR_S . $plsId;
        return $plsSysPath;
    }

    /**
     * Get playlist backup path in $_config[playlistsPath] dir
     * @param string $plsId
     * @return string Playlist path
     */
    static function getPlaylistBckpPath($plsId)
    {
        $plsBckpPath = self::$_config[playlistsPath] . DIR_S . 'backups' . DIR_S . $plsId;
        return $plsBckpPath;
    }

    /**
     * Get playlist store path in $_config[downloadPath] dir
     * @param string $plsId
     * @param string $type [genre|mood|tempo] 
     * @param string $level [1,2,3]
     * @return string Playlist path
     */
    static function getPlaylistPath($plsId, $type, $level)
    {
        $plsName = self::$_config[$type . PlsPrefix] . $plsId . '.m3u';
        $plsPath = self::$_config[downloadPath] . DIR_S . self::$_config[plsDir_L . $level] . DIR_S . $plsName;
        $plsPath = str_replace(DIR_S . DIR_S, DIR_S, $plsPath); // Fix double slash
        return $plsPath;
    }

    /**
     * Check if playlist contains track or not
     * @param string $plsSysPath Absolute path to playlist
     * @param string $trackPath Repative path to track
     * @return boolean
     */
    static function playlistContains($plsId, $trackPath)
    {
        $contains   = false;
        $plsSysPath = self::getPlaylistSysPath($plsId);

        // Check if dest playlist file exists
        if (is_file($plsSysPath) == false) {
            return false;
        }

        // checking if track already in playlist
        $tracks = file($plsSysPath);
        foreach ($tracks as $track) {
            if (strtolower(trim($track)) == strtolower(trim($trackPath))) {
                $contains = true;
                break;
            }
        }

        return $contains;
    }


    /**
     * Fix disallowed characters in file/dir name
     * @param string $name File or Dir name
     * @return string
     */
    static function fixName($name)
    {
        $name = str_replace('->', '_', $name);
        $name = str_replace('/', ', ', $name);
        $name = preg_replace("~[\\\/:*?\"<>|]~", ' ', $name);
        $name = preg_replace("~ \s* ~", ' ', $name);
        return $name;
    }

    /**
     * Filter $title, $album and $artist
     * @param string $string Title, Album or Artist
     * @param string $mode ['track', 'album', 'artist'] - way to filter
     * @return string
     */
    static function filterName($string, $mode) {
        if ($mode == 'track') {
            $string = str_ireplace('(Instrumental)', null, $string);
        }

        if ($mode == 'album') {
            $string = str_ireplace('(Single)', null, $string);
            $string = str_ireplace('(Explicit)', null, $string);
            $string = str_ireplace('(Radio Single)', null, $string);
            // $string = str_ireplace('(Soundtrack)', null, $string);
            // $string = str_ireplace('(Radio Edit)', null, $string);
            // $string = str_ireplace('(Bonus Track Version)', null, $string);
        }

        if ($mode == 'artist') {
            
        }

        $string = trim($string);

        return $string;
    }


    /**
     * Implode array into string with $glue
     * @param string $glue 
     * @param array $array
     * @return string
     */
    static function simplexmlImplode($glue, $array)
    {
        $implodeString = array();
        if ($array) {
            foreach ($array AS $value) {
                $implodeArray[] = strval($value);
            }
            $implodeString = implode($glue, $implodeArray);
        }
        return $implodeString;
    }


    /**
     * Format comment tag from $meta tags
     * @param arary $meta
     * @return array
     */
    static function getMetaComment($meta)
    {
        foreach ($meta as $key => $val) {
            if (stristr($key, 'TR_') OR stristr($key, 'ART_')) {
                $comment .= $key . ':'.$val.'; ' . "";
            }
        }
        
        return $comment;
    }


    static function formatMeta($meta, $ext)
    {
        $tags = null;
        foreach ($meta as $tag => $value) {

            // Replace tag with it's synonym as in _tagFilter
            if (self::$_tagFilter[$ext][$tag] != null) {
                $tag = self::$_tagFilter[$ext][$tag];
            }

            // Check if tag exist for selected $ext
            if (in_array($tag, self::$_tagFilter[$ext]) != true) {
                $tag = null;
            }

            if ($tag != null) {
                if ($ext == 'm4a') {
                    $tags .= '--' . $tag. ' "' . $value . '" ';
                }
                if ($ext == 'mp3') {
                    $tags .= '-metadata ' . $tag. '="' . $value . '" ';
                }
            }
        }

        return $tags;
    }


    /**
     * Return file extention according o mime type
     * @param string $mime optional Mime type like "audio/mpeg"
     * @return string File ext like "mp3"
     */
    static function getExtByMime($mime)
    {
        switch ($mime) {
            // Audio
            case 'audio/mpeg': $ext = 'mp3'; break;
            case 'audio/mp4':  $ext = 'm4a'; break;

            // Video

            // Images
            case 'image/jpeg': $ext = 'jpg'; break;
            case 'image/png':  $ext = 'png'; break;
            case 'image/gif':  $ext = 'gif'; break;

            default: $ext = ''; break;
        }

        return $ext;
    }
}


