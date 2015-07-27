<?php
/**
 * MusicTug helper class
 */
class MusicTugHelper
{
    private static $_config     = array();
    private static $_t1         = null;
    private static $_pid        = null;

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

        if (!self::$_config[logLevel]) {
            self::$_config[logLevel] = array();
        }
    }

    /**
     * Create config file with default values
     */
    private static function _createConfig()
    {
        $configText .= '<?php' . "\r\n";
        $configText .= '$mtConfig[version]             = \'20141028\';' . "\r\n";
        $configText .= "\r\n";
        $configText .= '// Must be filled:' . "\r\n";
        $configText .= '$mtConfig[lastfmKey]           = \'\';' . "\r\n";
        $configText .= '$mtConfig[gracenoteClientId]   = \'\';' . "\r\n";
        $configText .= '$mtConfig[gracenoteUserId]     = \'\';' . "\r\n";
        $configText .= '$mtConfig[gracenoteHost]       = \'https://208.72.242.176/webapi/xml/1.0/\';' . "\r\n";
        $configText .= '$mtConfig[curlProxy]           = \'\';' . "\r\n";
        $configText .= "\r\n";
        $configText .= '$mtConfig[downloadPath]        = \'E:\Music\_musicTug-test-1\';' . "\r\n";
        $configText .= '$mtConfig[tmpPath]             = \'.\_tmp\';' . "\r\n";
        $configText .= '$mtConfig[playlistsPath]       = \'.\_playlists\';' . "\r\n";
        $configText .= '$mtConfig[shell]               = \'cmd\'; // {\'cmd\'|\'powershell\'|\'unixshell\'}' . "\r\n";
        $configText .= '$mtConfig[powershellPath]      = \'%SYSTEMROOT%\System32\WindowsPowerShell\v1.0\';' . "\r\n";
        $configText .= "\r\n";
        $configText .= "// Tags, Lyrics, Artwork\r\n";
        $configText .= '$mtConfig[embedArtwork]        = true;' . "\r\n";
        $configText .= '$mtConfig[storeArtwork]        = true;' . "\r\n";
        $configText .= '$mtConfig[parseLyrics]         = true;' . "\r\n";
        $configText .= '$mtConfig[embedLyrics]         = true;' . "\r\n";
        $configText .= '$mtConfig[storeLyrics]         = true;' . "\r\n";
        $configText .= '$mtConfig[parseTags]           = true;' . "\r\n";
        $configText .= '$mtConfig[embedTags]           = true;' . "\r\n";
        $configText .= "\r\n";
        $configText .= "// Extantions, similar limit\r\n";
        $configText .= '$mtConfig[trackExt]            = \'m4a\';' . "\r\n";
        $configText .= '$mtConfig[artworkExt]          = \'jpg\';' . "\r\n";
        $configText .= '$mtConfig[similarMin]          = 60;'      . "\r\n";
        $configText .= "\r\n";
        $configText .= "// Playlists\r\n";
        $configText .= '$mtConfig[genrePlsStore]       = 3; // 0..3, 0 - to disable' . "\r\n";
        $configText .= '$mtConfig[moodPlsStore]        = 3;' . "\r\n";
        $configText .= '$mtConfig[tempoPlsStore]       = 3;' . "\r\n";
        $configText .= '$mtConfig[genrePlsPrefix]      = \'[G] \';' . "\r\n";
        $configText .= '$mtConfig[moodPlsPrefix]       = \'[M] \';' . "\r\n";
        $configText .= '$mtConfig[tempoPlsPrefix]      = \'[T] \';' . "\r\n";
        $configText .= '$mtConfig[plsDir_L1]           = \'\';' . "\r\n";
        $configText .= '$mtConfig[plsDir_L2]           = \'_pls-level-2\';' . "\r\n";
        $configText .= '$mtConfig[plsDir_L3]           = \'_pls-level-3\';' . "\r\n";

        $configText .= "\r\n";
        $configText .= "// \r\n";
        $configText .= '$mtConfig[logLevel]            = array('
                     . '\'init\',\'info\',\'stream\',\'file\',\'shell\',\'warning\',\'error\',\'playlist\''
                     . ');' . "\r\n";
        $configText .= '$mtConfig[logPath]             = \'.\_log\';' . "\r\n";

        file_put_contents(MT_CONFIG_FILE, $configText);
    }


    /**
     * Create lock file (Lock dir)
     * @param string $path Path to file that say's that dir is locked
     */
    static function lockDir($path)
    {
        MusicTugHelper::log("Locking dir");
        
        $f = fopen($path, 'w');
        fwrite($f, time());
        fclose($f);
    }

    /**
     * Delete lock file (Unlock dir)
     * @param string $path Path to file that say's that dir is locked
     */
    static function unlockDir($path)
    {
        MusicTugHelper::log("Unlocking dir");

        if (is_file($path)) {
            unlink($path);
        }
    }

    /**
     * Check if dir is locked. If .lock file isn't created or it's older then 300s - true
     * @param string $path Path to file that say's that dir is locked
     * @return boolean Dir locked or not
     */
    static function isLockedDir($path)
    {
        $isLocked = false;

        if (is_file($path)) {
            if (time() > file_get_contents($path) + 300) {
                // If .lock is older than 300s - unlicking
                MusicTugHelper::unlockDir($path);
            } else {
                // Else retrun trye
                $isLocked = true;
            }
        }

        return $isLocked;
    }

    /**
     * Write log
     * @param string $msg Log message
     * @param string $type Log type, one of 'init', 'info', 'stream', 'file', 'shell', 'warning', 'error', 'playlist'
     * @param string $args Additional text to $msg
     * @return bool Log write status
     */
    static function log($msg = null, $type = 'info', $args = null)
    {
        self::$_pid = (self::$_pid) ? : substr_replace(md5(microtime(true)), null, rand(2,5));
        $type       = strtolower($type);

        if (!$msg OR in_array($type, self::$_config[logLevel])) {
            $logPath    = self::$_config[logPath] . DIR_S . 'musictug.log';
            $backtrace  = debug_backtrace();
            if ($backtrace[1]['class'] AND $backtrace[1]['function']) {
                $mehtod = $backtrace[1]['class'] . '::' . $backtrace[1]['function'];
            }
            self::$_t1  = (self::$_t1) ? : T1;
            $time       = round(microtime(true) - self::$_t1, 3);
            self::$_t1  = microtime(true);

            if (!$msg) {
                $msgLog    = "\r\n";
            } else {
                $date      = date('m-d H:i:s');
                $pid       = str_pad(self::$_pid, 5);
                $time      = str_pad($time, 7);
                $type      = ($type == 'error') ? '*' . $type . '*' : $type;
                $type      = str_pad(strtoupper($type), 8);
                $methodMsg = str_pad($mehtod, 26);
                $argsMsg   = ($args) ? '  -  ' . implode(', ', $args) : null;
                $msgLog    = $date
                           . ' | ' . $pid
                           . ' | ' . $time 
                           . ' | ' . $type 
                           . ' | ' . $methodMsg 
                           . ' | ' . $msg . $argsMsg 
                           . "\r\n";
            }
            
            error_log($msgLog, 3, $logPath);
            return true;
        }

        return false;
    }

    /**
     * Print JSON response and exit
     * @param string $jsonStatus optional Status of JSON response success|error|fail
     * @param string|array $jsonData optional response data
     * @param string $errorMsg optional Message with error text
     */
    static function jsonResponse($jsonStatus = 'fail', $jsonData = null, $errorMsg = null)
    {
        $statusArray = array('success', 'error', 'fail');
        if (!in_array($jsonStatus, $statusArray)) {
            $jsonStatus = 'fail';
        }

        $jsonInfo = array(
            ts          => time(),
            errorMsg    => $errorMsg
        );

        $jsonResponse = array(
            status  => $jsonStatus, 
            info    => $jsonInfo, 
            data    => $jsonData, 
        );

        $json = json_encode($jsonResponse);

        header('Content-Type: application/json');
        echo $json;
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
	
	/**
     * Print result of GraceNote requeest
     */
    static function testGracenote()
    {
        $config = self::getConfig();

        $curlPost = '<QUERIES>
            <LANG>eng</LANG>
            <AUTH>
                <CLIENT>' . $config[gracenoteClientId] . '</CLIENT>
                <USER>' . $config[gracenoteUserId] . '</USER>
            </AUTH>
            <QUERY CMD="ALBUM_SEARCH">
                <TEXT TYPE="ARTIST">flying lotus</TEXT>
                <TEXT TYPE="ALBUM_TITLE">until the quiet comes</TEXT>
                <TEXT TYPE="TRACK_TITLE">all in</TEXT>
            </QUERY>
        </QUERIES>';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,            $config[gracenoteHost]);
        curl_setopt($ch, CURLOPT_TIMEOUT,        60);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // false for SSL work
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); // false for SSL work
        curl_setopt($ch, CURLOPT_POST,           true);
        curl_setopt($ch, CURLOPT_POSTFIELDS,     $curlPost); 
        if ($config[curlProxy] != '') {
            curl_setopt($ch, CURLOPT_PROXY,      $config[curlProxy]);
        }

        $response = curl_exec($ch);
        curl_close($ch);

        dbg($response);
    }

}