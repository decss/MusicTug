<?php
/**
 * Main download class MusicTug
 */
class MusicTug
{
    use tagsStreamTrait;
    use playlistsTrait;
 
    private $_title      = null;
    private $_album      = null;
    private $_artist     = null;
    private $_artworkUrl = null;
    private $_trackUrl   = null;
    private $_options    = array();

    private $_config     = array();
    private $_driver     = null;

    private $_name       = array();
    private $_url        = array();
    private $_path       = array();
    private $_isExist    = array();
    private $_isSaved    = array();
    private $_stream     = array();
    private $_flags      = array();

    private $_tmp        = array();

    private $_noise      = array(
        // title  => array('(Instrumental)'),
        // album  => array('(Single)', '(Explicit)', '(Radio Single)'),
        artist => array('Various Artists', 'Hybrid/Various Artists'),
    );


    function __construct($trackData, $options = array())
    {
        // Set config
        $this->_title       = trim($trackData[title]);
        $this->_album       = trim($trackData[album]);
        $this->_artist      = trim($trackData[artist]);
        $this->_artworkUrl  = trim($trackData[artworkUrl]);
        $this->_trackUrl    = trim($trackData[trackUrl]);
        $this->_options     = $options;
        $this->_config      = MusicTugHelper::getConfig();

        if ($this->_title == null OR $this->_album == null OR $this->_artist == null) {
            throw new Exception('At least $title, $album AND $atist must be specified');
        }

        $this->_name[title]       = MusicTugHelper::fixName($this->_title);
        $this->_name[album]       = MusicTugHelper::fixName($this->_album);
        $this->_name[artist]      = MusicTugHelper::fixName($this->_artist);
        $this->_name[artwork]     = MusicTugHelper::fixName($this->_album);
        $this->_url[track]        = str_replace(' ', '%20', $this->_trackUrl);
        $this->_url[artwork]      = str_replace(' ', '%20', $this->_artworkUrl);
        $this->_path[relative]    = $this->_name[artist] . DIR_S . $this->_name[album];
        $this->_path[absolute]    = $this->_config[downloadPath] . DIR_S . $this->_path[relative];
        $this->_path[trackRel]    = $this->_path[relative] . DIR_S . $this->_name[title] . '.' . $this->_config[trackExt];
        $this->_path[trackAbs]    = $this->_path[absolute] . DIR_S . $this->_name[title] . '.' . $this->_config[trackExt];
        $this->_path[artwork]     = $this->_path[absolute] . DIR_S . $this->_name[artwork] . '.' . $this->_config[artworkExt];
        $this->_path[lyrics]      = $this->_path[absolute] . DIR_S . 'lyrics' . DIR_S . $this->_name[title] . '.txt';
        $this->_isExist[track]    = is_file($this->_path[trackAbs]) ? : false;
        $this->_isExist[artwork]  = is_file($this->_path[artwork]) ? : false;
        $this->_isExist[lyrics]   = is_file($this->_path[lyrics]) ? : false;
        $this->_isSaved[track]    = null;
        $this->_isSaved[artwork]  = null;
        $this->_isSaved[lyrics]   = null;
        $this->_stream[track]     = array();
        $this->_stream[artwork]   = array();
        $this->_stream[lyrics]    = array();
        $this->_stream[tags]      = array();

        $this->_flags[track]      = array(
            stream    => null,    // null, true, false  
            success   => null,    // null, true, false
        );
        $this->_flags[artwork]    = array(
            stream    => null,
            success   => null,
        );
        $this->_flags[lyrics]     = array(
            stream    => null,
            success   => null,
        );
        $this->_flags[tags]       = array(
            stream    => null,
            success   => null,
        );

        // Create system dirs
        $this->_createDirs('system');

        dbg($this);
    }


    function init()
    {
        // Create media dirs
        $this->_createDirs('media');


        // Track stream
        if ($this->_url[track] AND !$this->_isExist[track]) {
            $this->_stream[track] = $this->getTrackStream();
        }

        // Artwork stream
        if ($this->_url[artwork] 
            AND !$this->_isExist[artwork]
            AND ($this->_config[embedArtwork] OR $this->_config[storeArtwork])
        ) {
            $this->_stream[artwork] = $this->getArtworkStream();
        }
        // Tags stream
        if ($this->_config[embedTags]) { // TODO : Уточнить условия, когда парсить тэги !
            if ($this->_config[parseTags]) {
                $this->_stream[tags] = $this->getTagsStream();
            } else {
                $this->_stream[tags] = $this->_getTagsArray();
            }
        }

        // Lyrics stream
        if (!$this->_isExist[lyrics]
            AND $this->_config[parseLyrics]
            AND ($this->_config[embedLyrics] OR $this->_config[storeLyrics] )
        ) {
            $this->_stream[lyrics] = $this->getLyricsStream();
        }
        // ::: DEBUG :::
        // $this->_stream[track][file]   = null;
        // $this->_stream[artwork][file] = null;
        // dbg($this->_stream[tags], 0);
        // dbg($this->_flags, 0);
        // dbg($this->_stream, 0);
        // exit;



        // Save track stream into file
        if ($this->_flags[track][success] === true) {
            // Write
            $trackPath = $this->_path[absolute] . DIR_S . $this->_name[title] . '.' . $this->_stream[track][ext];
            $this->_saveStream($this->_stream[track][file], $trackPath, true);
            // Convert track
            if ($this->_config[trackExt] AND $this->_config[trackExt] != $this->_stream[track][ext]) {
                $trackPath = $this->_convert($trackPath, $this->_path[trackAbs]);
            }

            $this->_isSaved[track] = $trackPath;
        }
        // Save artwork stream into file
        if ($this->_flags[artwork][success] === true) {
            // Write
            $artworkPath = $this->_path[absolute] . DIR_S . $this->_name[artwork] . '.' . $this->_stream[artwork][ext];
            $this->_saveStream($this->_stream[artwork][file], $artworkPath, true);

            // Convert artwork
            if ($this->_config[artworkExt] AND $this->_config[artworkExt] != $this->_stream[artwork][ext]) {
                $artworkPath = $this->_convert($artworkPath, $this->_path[artwork]);
            }

            $this->_isSaved[artwork] = $artworkPath;
        }



        $isTrackSaved   = ($this->_isSaved[track] AND !$this->_isExist[track]);
        $isArtworkExist = ($this->_isSaved[artwork] OR $this->_path[artwork]);
        $isLyricsExist  = ($this->_isSaved[lyrics] OR $this->_isExist[lyrics]);


        // Embed artwork
        if ($this->_config[embedArtwork] AND $isTrackSaved AND $isArtworkExist) {
            $this->_embedArtwork();
        }
        // Store artwork. If storeArtwork = false then delete artwork file (stored above)
        if (!$this->_config[storeArtwork] AND $this->_isSaved[artwork]) {
            unlink($this->_isSaved[artwork]);
        }


        // Save lyrics
        if ($this->_flags[lyrics][success] === true) {
            $lyricsPath = $this->_path[lyrics];
            $this->_saveStream($this->_stream[lyrics][lyrics], $lyricsPath, true);

            $this->_isSaved[lyrics] = $lyricsPath;
        }
        // Embed lyrics
        if ($this->_config[embedLyrics] AND $isTrackSaved AND $isLyricsExist) {
            $this->_embedLyrics();
        }
        // Store lyrisc. If storeLyrics = false then delete lyrics file (stored above)
        if (!$this->_config[storeLyrics] AND $this->_isSaved[lyrics]) {
            unlink($this->_isSaved[lyrics]);
            $scandir = scandir(dirname($this->_isSaved[lyrics]));
            if (count($scandir) == 2) {
                rmdir(dirname($this->_isSaved[lyrics]));
            }
        }


        // Tags
        if ($this->_config[embedTags] AND !$this->_isExist[track] AND $this->_stream[tags][meta]) {
            $this->_embedTags();
        }


        // Playlists
        if ($this->_config[genrePlsStore] OR $this->_config[moodPlsStore] OR $this->_config[tempoPlsStore]) {
            $this->_storePlaylists();
        }


    }





    /**
     * Embed tags into track file
     */
    private function _embedTags()
    {
        $shell   = null;
        $inPath  = $this->_path[trackAbs];
        $outPath = $this->_path[trackAbs] . '.tags.' . $this->_config[trackExt];
        $meta    = MusicTugHelper::formatMeta($this->_stream[tags][meta], $this->_config[trackExt]);
        
        if ($this->_config[trackExt] == 'm4a') {
            $shell = 'AtomicParsley.exe "' . $inPath . '" ' . $meta . ' --output "' . $outPath . '"';
        } else {
            $shell = 'ffmpeg.exe -i "' . $inPath . '" -acodec copy ' . $meta . ' "' . $outPath . '" -y';
        }
        if ($shell AND $meta) {
            exec($shell);
        }

        if (is_file($outPath)) {
            unlink($inPath);
            rename($outPath, $inPath);
        }
    }


    /**
     * Embed lyrics into track file
     */
    private function _embedLyrics()
    {
        if (is_file($this->_isSaved[lyrics]) == true) {
            $lyricsPath = $this->_isSaved[lyrics];
        } elseif ($this->_isExist[lyrics] == true) {
            $lyricsPath = $this->_path[lyrics];
        } else {
            $lyricsPath = null;
        }

        if ($lyricsPath == null) {
            return false;
        }

        $shell   = null;
        $inPath  = $this->_path[trackAbs];
        $outPath = $this->_path[trackAbs] . '.lyrics.' . $this->_config[trackExt];
        
        if ($this->_config[trackExt] == 'm4a') {
            $shell = 'AtomicParsley.exe "' . $inPath . '" --lyricsFile "' . $lyricsPath . '" --output "' . $outPath . '"';
        } else {
            // NOT SUPPORTED
        }

        if ($shell) {
            exec($shell);
        }
        if (is_file($outPath)) {
            unlink($inPath);
            rename($outPath, $inPath);
        }
    }


    /**
     * Embed artwork into track file
     */
    private function _embedArtwork()
    {
        if (is_file($this->_isSaved[artwork]) == true) {
            $artworkPath = $this->_isSaved[artwork];
        } elseif ($this->_isExist[artwork] == true) {
            $artworkPath = $this->_path[artwork];
        } else {
            $artworkPath = null;
        }

        if ($artworkPath == null) {
            return false;
        }

        $shell   = null;
        $inPath  = $this->_path[trackAbs];
        $outPath = $this->_path[trackAbs] . '.artwork.' . $this->_config[trackExt];
        if ($this->_config[trackExt] == 'm4a') {
            $shell = 'AtomicParsley.exe "' . $inPath . '" --artwork "' . $artworkPath . '" --output "' . $outPath . '"';
        } else {
            $options = '-map_metadata 0 -map 0 -map 1 -acodec copy';
            $shell   = 'ffmpeg.exe -i "' . $inPath . '" -i "' . $artworkPath . '" ' . $options . ' "' . $outPath . '" -y';
        }

        if ($shell) {
            exec($shell);
        }
        if (is_file($outPath)) {
            unlink($inPath);
            rename($outPath, $inPath);
        }
    }


    /**
     * Convert track file
     * @param string $path
     * @param string $path2
     * @return string Path of converted file
     */
    private function _convert($path, $path2)
    {
        $options  = null;
        $pathExt  = substr_replace($path, null, 0, strripos($path, '.') + 1); 
        $path2Ext = substr_replace($path2, null, 0, strripos($path2, '.') + 1);

        if ($pathExt == 'jpg' AND $path2Ext == 'gif') {
            $options = "-pix_fmt rgb24";
        }

        $shell  = "ffmpeg.exe -i \"$path\" $options \"$path2\" -y";
        exec($shell);

        if (is_file($path) == true AND $path != $path2) {
            unlink($path);
        }

        if (is_file($path2) != true) {
            $path2 = null;
        }

        return $path2;
    }


    /**
     * Download file and info(headers, ...) by url
     * @param string $url File url to download via CURL
     * @return array Array() on fail or Array('info', 'file') on success
     */
    private function _openStream($url)
    {
        $stream = array();
        $ch     = curl_init();
        curl_setopt($ch, CURLOPT_URL,            $url);
        curl_setopt($ch, CURLOPT_TIMEOUT,        240);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        if ($this->_config[curlProxy] != null) {
            curl_setopt($ch, CURLOPT_PROXY,      $this->_config[curlProxy]);
        }

        $file = curl_exec($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);

        if ($info[http_code] == 200) {
            $stream[ext]  = MusicTugHelper::getExtByMime($info[content_type]);
            $stream[info] = $info;
            $stream[file] = $file;
        }

        return $stream;
    }


    /**
     * Save stream into file (path)
     * @param string $file Stream to write into file
     * @param string $path Path to save file
     * @param boolean $rewrite optional Rewrite existing file or not
     * @return boolean true or false
     */
    private function _saveStream($file, $path, $rewrite = true)
    {
        if ($rewrite == false AND is_file($path) == true) {
            return false;
        }

        // Check if path dirs exist an create if not
        $pathCheck = dirname($path);
        if (is_dir($pathCheck) == false) {
            mkdir($pathCheck, 0755, true);
        }

        $fp = fopen($path, 'w');
        fwrite($fp, $file);
        fclose($fp);

        if (is_file($path) == true) {
            return true;
        }

        return false;
    }


    /**
     * Download track and return in in array
     * @return array
     */
    function getTrackStream()
    {
        if ($this->_url[track] == null) {
            throw new Exception('trackUrl must be specified to stream track');
        }
        $trackStream = $this->_openStream($this->_url[track]);

        $this->_flags[track][stream]  = true;
        $this->_flags[track][success] = (bool)$trackStream[file];

        return $trackStream;
    }


    /**
     * Download cover and return in in array
     * @retirn array
     */
    function getArtworkStream()
    {
        if ($this->_url[artwork] == null) {
            throw new Exception('artworkUrl must be specified to stream artwork');
        }
        $artworkStream = $this->_openStream($this->_url[artwork]);

        $this->_flags[artwork][stream]  = true;
        $this->_flags[artwork][success] = (bool)$artworkStream[file];

        return $artworkStream;
    }




    /**
     * Parse lyrics on http://lyrics.wikia.com
     * @return array Lyrics Array('requestUrl', 'pageUrl', 'header', 'lyrics', 'chars', 'rows')
     */
    function getLyricsStream()
    {
        $title  = $this->_title;
        $artist = $this->_artist;
        $apiUrl = 'http://lyrics.wikia.com/api.php?func=getSong&fmt=xml&action=lyrics';

        $reqUrl = $apiUrl . '&song=' . $title . '&artist=' . $artist;
        $xml    = simplexml_load_file($reqUrl);

        if ($xml->lyrics == 'Not found') {
            // Try to use title, album, artist from parsed tags
            $tags = $this->getTagsStream();

            if ($tags[meta]) {
                $title  = str_replace(' ', '%20', $tags[meta][title]);
                $album  = str_replace(' ', '%20', $tags[meta][album]);
                $artist = str_replace(' ', '%20', $tags[meta][artist]);
            }
        }

        // Try with tags - Title, Artist 
        if ($xml->lyrics == 'Not found' AND $title AND $artist) {
            $reqUrl = $apiUrl . '&song=' . $title . '&artist=' . $artist;
            $xml    = simplexml_load_file($reqUrl);
        }

        // Try with tags - Title, Artist, Album
        if ($xml->lyrics == 'Not found' AND $title AND $artist AND $album) {
            $reqUrl = $apiUrl . '&song=' . $title . '&artist=' . $artist . '&albumName=' . $album;
            $xml    = simplexml_load_file($reqUrl);
        }

        if ($xml->lyrics != 'Not found') {
            $lyricsUrl  = urldecode((string)$xml->url);
            $lyricsUrl  = str_replace('?', '%3F', $lyricsUrl);
            $lyricsPage = file_get_contents($lyricsUrl);
        }

        if ($lyricsPage) {
            // TODO: make parsing when links are displayed instead of lyrics
            $lyricsHeader = substr_replace($lyricsPage, null, 0, strpos($lyricsPage, 'class="WikiaPageHeader"'));
            $lyricsHeader = substr_replace($lyricsHeader, null, 0, strpos($lyricsHeader, "<h1>") + strlen('<h1>'));
            $lyricsHeader = substr_replace($lyricsHeader, null, strpos($lyricsHeader, "</h1>"));
            $lyricsHeader = preg_replace("~\s?lyrics$~i", null, $lyricsHeader);

            $lyrics = substr_replace($lyricsPage, null, 0, strpos($lyricsPage, "<div class='lyricbox'>"));
            $lyrics = substr_replace($lyrics, null, 0, strpos($lyrics, '&#'));
            if (strstr($lyrics, "<div class='rtMatcher'>")) {
                $lyrics = substr_replace($lyrics, null, strpos($lyrics, "<div class='rtMatcher'>"));
            }
            if (strstr($lyrics, '<!--')) {
                $lyrics = substr_replace($lyrics, null, strpos($lyrics, '<!--'));
            }

            $lyrics = html_entity_decode($lyrics);
            $lyrics = str_replace("<br />", "\r\n", $lyrics);
            $lyrics = str_replace("&#39;", "'", $lyrics);
            $lyrics = strip_tags($lyrics);

            if (stristr($lyrics, 'Unfortunately, we are not licensed to display') == true) {
                $lyrics = substr_replace($lyrics, null, strpos($lyrics, 'Unfortunately, we are not licensed to display'));
                $lyrics = trim($lyrics);
                $lyrics .= "\r\n\r\n" . "--- not full lyrics ---";
            }
            $lyrics = trim($lyrics);
        }

        if (strlen($lyrics) <= 24) {
            $lyrics = null;
        }

        // Set flags
        $this->_flags[lyrics][stream]  = true;
        $this->_flags[lyrics][success] = (bool)$lyrics;

        // Set return
        $return[requestUrl] = $reqUrl;
        $return[pageUrl]    = $lyricsUrl;
        $return[header]     = $lyricsHeader;
        $return[lyrics]     = $lyrics;
        $return[chars]      = strlen($lyrics);
        $return[rows]       = substr_count(str_replace("\r\n\r\n", "\r\n", trim($lyrics)), "\r\n");
        return $return;
    }


    /**
     * Create /tmp, /stations, /logs, _config[downloadPath] and _path[absolute] folders
     * @param string $mode optional [system, media, both]
     */
    private function _createDirs($mode = 'system')
    {

        if ($mode == 'system' OR $mode == 'both') {
            if (!is_dir($this->_config[tmpPath])) {
                mkdir($this->_config[tmpPath], 755, true);
            }
            if (!is_dir($this->_config[playlistsPath])) {
                mkdir($this->_config[playlistsPath], 755, true);
                mkdir($this->_config[backupDir] . DIR_S . 'backups', 755, true);
            }
            // TODO: add "logs" dir 
        }

        if ($mode == 'media' OR $mode == 'both') {
            if (!is_dir($this->_config[downloadPath])) {
                mkdir($this->_config[downloadPath], 755);
            }
            if (!is_dir($this->_path[absolute])) {
                mkdir($this->_path[absolute], 755, true);
            }

            if (!is_dir($this->_config[downloadPath] . DIR_S . $this->_config[plsDir_L1])) {
                mkdir($this->_config[downloadPath] . DIR_S . $this->_config[plsDir_L1], 755, true);
            }
            if (!is_dir($this->_config[downloadPath] . DIR_S . $this->_config[plsDir_L2])) {
                mkdir($this->_config[downloadPath] . DIR_S . $this->_config[plsDir_L2], 755, true);
            }
            if (!is_dir($this->_config[downloadPath] . DIR_S . $this->_config[plsDir_L3])) {
                mkdir($this->_config[downloadPath] . DIR_S . $this->_config[plsDir_L3], 755, true);
            }

        }
    }
}









trait playlistsTrait
{
    /**
     * Save playlists, make backups
     * @param array $plsArr Array with playlists names
     */
    private function _storePlaylists()
    {
        $plsArr    = $this->_getPlaylistsId();
        $trackPath = $this->_path[trackRel];

        foreach ($plsArr as $type => $array) {
            foreach ($array as $level => $plsId) {
                $plsName = $this->_config[$type . PlsPrefix] . $plsId . '.m3u';
                $plsPath = MusicTugHelper::getPlaylistPath($plsId, $type, $level);

                if (MusicTugHelper::playlistContains($plsId, $trackPath) == false) {
                    $this->_backupPlaylist($plsId);
                    $this->_updatePlaylist($plsId, $trackPath);
                    $this->_copyPlaylist($plsId, $plsPath);
                }
            }
        }
    }


    /**
     * Update playlist
     * @param string $plsId
     * @param string $trackPath
     *
     */
    private function _updatePlaylist($plsId, $trackPath)
    {
        $plsSysPath = MusicTugHelper::getPlaylistSysPath($plsId);

        if (!$trackPath) {
            return false;
        }

        // Create new playlist if not exist
        if (is_file($plsSysPath) == false) {
            $f = fopen($plsSysPath, 'w');
            fwrite($f, '# ID: ' . $plsId . "\r\n");
            fclose($f);
        } 

        // Add track into playlist
        $f = fopen($plsSysPath, 'a');
        fwrite($f, $trackPath."\r\n");
        fclose($f);

        return true;
    }


    /**
     * Copy system playlist
     * @param string $plsId
     * @param string $plsPath
     */
    private function _copyPlaylist($plsId, $plsPath)
    {
        $plsSysPath = MusicTugHelper::getPlaylistSysPath($plsId);

        if (is_file($plsPath) == true) {
            unlink($plsPath);
        }

        copy($plsSysPath, $plsPath);
    }


    /**
     *
     * @param string $plsId
     */
    private function _backupPlaylist($plsId) {
        $plsSysPath  = MusicTugHelper::getPlaylistSysPath($plsId);
        $plsBckpPath = MusicTugHelper::getPlaylistBckpPath($plsId);
        $sysCnt      = 0;
        $bckpCnt     = 0;

        if (is_file($plsSysPath)) {
            $sysCnt  = count(file($plsSysPath));
        }

        if (is_file($plsBckpPath)) {
            $bckpCnt = count(file($plsBckpPath));
        }
        
        if ($sysCnt >= $bckpCnt + 20) {
            copy($plsSysPath, $plsBckpPath . '.' . date("Y-m-d_H-i-s") . '.bak');
        }
    }



    /**
     * Make array with playlists names
     */
    private function _getPlaylistsId()
    {
        $plsArr    = array();

        if ($this->_flags[tags][success] == true AND $this->_stream[tags][meta]) {
            $chain[genre] = explode('->', $this->_stream[tags][meta][TR_GENRE]);
            $chain[mood]  = explode('->', $this->_stream[tags][meta][TR_MOOD]);
            $chain[tempo] = explode('->', $this->_stream[tags][meta][TR_TEMPO]);
        }

        // make array filled with playlist names
        foreach ($chain as $type => $array) {
            $maxLevel      = $this->_config[$type . PlsStore];
            $playlistChain = null;

            for ($i = 1; $i <= $maxLevel; $i++) {
                if (!$chain[$type][$i - 1]) {
                    continue;
                }
                if ($playlistChain != null) {
                    $playlistChain .= ' -- ';
                }
                $playlistChain    .= $chain[$type][$i - 1];

                $plsArr[$type][$i] = $playlistChain;
            }
        }

        return $plsArr;
    }

}








trait tagsStreamTrait
{
    /**
     * Make $tags tags array from inpur $title, $album and $artist
     * @return array Metatags array
     */
    private function _getTagsArray()
    {
        $tags = array();
        
        if ($this->_title) {
            $meta[title]  = $this->_title;
        }
        if ($this->_album) {
            $meta[album]  = $this->_album;
        }
        if ($this->_artist) {
            $meta[artist] = $this->_artist;
        }
        if ($meta) {
            $meta[comment]    = MusicTugHelper::getMetaComment($meta);
        }

        $tags = array(
            origin       => 'local',
            opt          => null,
            similarIndex => null,
            meta         => $meta,
        );

        return $tags;
    }


    /**
     * Return $this->_tmp[tagsStream] with the bigest "similarIndex"
     * @return array One oF $this->_tmp[tagsStream][] items with best "similarIndex"
     */
    function getTagsStream()
    {
        $streamIndex  = null;
        $similarIndex = null;
        $tagsStream   = array();

        if ($this->_tmp[tagsStream] == null) {
            $this->_storeTagsStream();
        }

        foreach ($this->_tmp[tagsStream] as $key => $tags) {
            if ($tags[similarIndex] >= $similarIndex AND $tags[similarIndex] >= $this->_config[similarMin]) {
                $streamIndex  = $key;
                $similarIndex = $tags[similarIndex];
            }
        }

        $success = false;
        if ($streamIndex !== null) {
            $tagsStream = $this->_tmp[tagsStream][$streamIndex];
            $success    = true;
        }

        // Set flags
        $this->_flags[tags][stream]  = true;
        $this->_flags[tags][success] = $success;
        
        return $tagsStream;
    }


    /**
     * Parse metatags on http://www.gracenote.com/ 
     */
    private function _storeTagsStream()
    {
        if (!$this->_config[gracenoteClientId] OR !$this->_config[gracenoteUserId]) {
            throw new Exception('Gracenote Client ID or User ID is not specified');
        }

        $genreNum = 0;
        $optArray = array(
            array(), 
            array('no-album'), 
            // array('no-artist'),
        );

        $title    = MusicTugHelper::filterName($this->_title, 'track');
        $album    = MusicTugHelper::filterName($this->_album, 'album');
        $artist   = MusicTugHelper::filterName($this->_artist, 'artist');
     
        foreach ($optArray as $opt) {
            $tags           = array();
            $meta           = array();
            $curlPostFields = null;
            if ($title) {
                $curlPostFields .= '<TEXT TYPE="TRACK_TITLE">' . htmlspecialchars($title) . '</TEXT>';
            }
            if ($album AND in_array('no-album', $opt) == false) {
                $curlPostFields .= '<TEXT TYPE="ALBUM_TITLE">' . htmlspecialchars($album) . '</TEXT>';
            }
            if ($artist AND in_array('no-artist', $opt) == false) {
                $curlPostFields .= '<TEXT TYPE="ARTIST">' . htmlspecialchars($artist) . '</TEXT>';
            }

            $curlPost = '<QUERIES>
                <LANG>eng</LANG>
                <AUTH>
                    <CLIENT>' . $this->_config[gracenoteClientId] . '</CLIENT>
                    <USER>' . $this->_config[gracenoteUserId] . '</USER>
                </AUTH>
                <QUERY CMD="ALBUM_SEARCH">
                    <MODE>SINGLE_BEST</MODE>
                    ' . $curlPostFields . '
                    <OPTION>
                        <PARAMETER>SELECT_EXTENDED</PARAMETER>
                        <VALUE>MOOD,TEMPO,ARTIST_OET</VALUE>
                    </OPTION>
                    <OPTION>
                        <PARAMETER>SELECT_DETAIL</PARAMETER>
                        <VALUE>GENRE:3LEVEL,MOOD:2LEVEL,TEMPO:3LEVEL,ARTIST_ORIGIN:4LEVEL,ARTIST_ERA:2LEVEL,ARTIST_TYPE:2LEVEL</VALUE>
                    </OPTION>
                </QUERY>
            </QUERIES>';
            // Short request
            // $curlPost = '<QUERIES>
            //     <LANG>eng</LANG>
            //     <AUTH>
            //         <CLIENT>' . $this->_config[gracenoteClientId] . '</CLIENT>
            //         <USER>' . $this->_config[gracenoteUserId] . '</USER>
            //     </AUTH>
            //     <QUERY CMD="ALBUM_SEARCH">
            //         <MODE>SINGLE_BEST</MODE>
            //         ' . $curlPostFields . '
            //     </QUERY>
            // </QUERIES>';

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL,            $this->_config[gracenoteHost]);
            curl_setopt($ch, CURLOPT_TIMEOUT,        60);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // false for SSL work
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); // false for SSL work
            curl_setopt($ch, CURLOPT_POST,           true);
            curl_setopt($ch, CURLOPT_POSTFIELDS,     $curlPost); 
            if ($this->_config[curlProxy] != '') {
                curl_setopt($ch, CURLOPT_PROXY,      $this->_config[curlProxy]);
            }

            $response = curl_exec($ch);
            curl_close($ch);

            if ($response) {
                $xml = simplexml_load_string($response);
            }

            if ($xml->RESPONSE->attributes()->STATUS == 'OK') {
                $meta[title]      = (string)$xml->RESPONSE->ALBUM->TRACK->TITLE;
                $meta[album]      = (string)$xml->RESPONSE->ALBUM->TITLE;
                $meta[artist]     = (string)$xml->RESPONSE->ALBUM->ARTIST;
                // Various Artists, Hybrid/Various Artists FIX
                if (in_array($meta[artist], $this->_noise[artist]) == true) {
                    $meta[artist] = (string)$xml->RESPONSE->ALBUM->TRACK->ARTIST;
                }
                $meta[track]      = (string)$xml->RESPONSE->ALBUM->TRACK->TRACK_NUM;
                $meta[genre]      = (string)$xml->RESPONSE->ALBUM->GENRE[$genreNum];
                $meta[date]       = (string)$xml->RESPONSE->ALBUM->DATE;
                $tempoCount       = count($xml->RESPONSE->ALBUM->TRACK->TEMPO);
                $meta[tempo]      = (int)$xml->RESPONSE->ALBUM->TRACK->TEMPO[$tempoCount - 1];

                $meta[TR_GENRE]   = MusicTugHelper::simplexmlImplode('->', $xml->RESPONSE->ALBUM->GENRE);
                $meta[TR_MOOD]    = MusicTugHelper::simplexmlImplode('->', $xml->RESPONSE->ALBUM->TRACK->MOOD);
                $meta[TR_TEMPO]   = MusicTugHelper::simplexmlImplode('->', $xml->RESPONSE->ALBUM->TRACK->TEMPO);
                $meta[ART_ERA]    = MusicTugHelper::simplexmlImplode('->', $xml->RESPONSE->ALBUM->ARTIST_ERA);
                $meta[ART_ORIGIN] = MusicTugHelper::simplexmlImplode('->', $xml->RESPONSE->ALBUM->ARTIST_ORIGIN);
                $meta[ART_TYPE]   = MusicTugHelper::simplexmlImplode('->', $xml->RESPONSE->ALBUM->ARTIST_TYPE);

                $meta[comment]    = MusicTugHelper::getMetaComment($meta);

                // Remove empty metatags and fix tags
                foreach ($meta as $key => $value) {
                    if ($meta[$key] == null) {
                        unset($meta[$key]);
                    } else {
                        $meta[$key] = str_replace("\"", "'", $meta[$key]);
                        $meta[$key] = str_replace("\r\n", " ", $meta[$key]);
                    }
                }


                $tags = array(
                    origin       => 'remote',
                    opt          => $opt,
                    similarIndex => $this->_getSimilarIndex($meta),
                    meta         => $meta,
                );

                $this->_tmp[tagsStream][] = $tags;
            }
        }
    }


    /**
     * Calculate similar index between input title, album, artisi and parsed
     * @param array $meta Metatags array
     * @return int Similar index 0..100
     */
    private function _getSimilarIndex($array)
    {
        similar_text(strtolower($array[title]),   strtolower($this->_title),   $index[title]);
        similar_text(strtolower($array[album]),   strtolower($this->_album),   $index[album]);
        similar_text(strtolower($array[artist]),  strtolower($this->_artist),  $index[artist]);

        // Various Artists, Hybrid/Various Artists FIX
        foreach ($this->_noise[artist] as $artistNoise) {
            similar_text(strtolower($array[artist]), strtolower($artistNoise), $index[artistNoise]);
            if ($index[artistNoise] > $index[artist]) {
                $index[artist] = 50;
            }
        }

        $index[ttl] = round(($index[title] * 1.1 + $index[album] * 0.8 + $index[artist] * 1.1) / 3 , 2);

        return $index[ttl];
    }
}

