<style>
#mt-panel, #mt-panel table, #mt-panel a {
    color: #939393;
    line-height: 11px;
    font-size: 10px;
    /*font-family: "Lucida Console";*/
    font-family: "Verdana";
}
#mt-panel {
    border: 1px solid blue;
    width: 310px;
    height: 270px;
    background: black;
    padding: 4px;
}
#mt-panel table {width: 100%; border-collapse: collapse;}
#mt-panel #mt-orig-track, #mt-panel #mt-orig-album, #mt-panel #mt-orig-artist {color: white;}
#mt-panel table, #mt-panel .pad-bot {margin-bottom: 6px;}
#mt-panel table td {padding: 0;}
#mt-panel span, #mt-panel span a {color: #3399FF;}
#mt-panel span a {color: #6FBBFF;}
#mt-panel .mt-green {color: #70FF2D;}
#mt-panel .mt-red {color: #FF2D2D;}
</style>

<div id="mt-panel">
    <table>
        <tr><td>
            <div id="mt-orig-track">I'm Not Alone (Deadmau5 Mix)</div>
            <div><span id="mtTrack">I'm Not Alone (Deadmau5 Mix)</span></div>
        </td></tr>
        <tr><td>by</td></tr>
        <tr><td>
            <div id="mt-orig-album">Calvin Harris</div>
            <div><span id="mt-album">Calvin Harris</span></div>
        </td></tr>
        <tr><td>on</td></tr>
        <tr><td>
            <div id="mt-orig-artist">I'm Not Alone (Single)</div>
            <div><span id="mt-artist">I'm Not Alone (Single)</span></div>
        </td></tr>
    </table>
    <table>
        <tr><td>Tags: <span id="mt-status-tags"><span class="mt-green">Success</span></span></td></tr>
        <tr><td>
            <div>Chain:<span id="mt-chain">Alternative & Punk->Trance->Trance</span></div>
            <div>
                Genre:<span id="mt-genre">Alternative & Punk</span>, №:<span id="mt-num">1</span>, 
                Y:<span id="mt-year">2013</span>, BPM:<span id="mt-bpm">120</spam>
            </div>
            <div>Similar:<span id="mt-similar">93</span>% (from 65,77,93)</div>
        </td></tr>
    </table>
    <table>
        <tr><td>Lyrics: <span id="mt-status-tags"><span class="mt-green">Success</span></span></td></tr>
        <tr><td>
            <div>
                Links:<span id="mt-xml-url"><a  target="_blank" href="">[XML page]</a></span>
                <span id="mt-page-url"><a target="_blank" href="">[Lyrics page]</a></span>, 
                Length:<span id="mt-chars">1281/40</span>, 
            </div>
            <div>
                Title:<span id="mt-lyrics-title">Calvin Harris:I'm Not Alone (Deadmau5 Mix)</span>
            </div>
            <div>
                Text:<span id="mt-lyrics-text">Some Lyrics Text</span>
            </div>
        </td></tr>
    </table>
    <table>
        <tr><td style="width:45px;">Flags:</td><td></td></tr>
        <tr>
            <td>Track</td>
            <td>- <span id="mt-info-track"><span class="mt-green">Stored</span></span></td>
        </tr>
        <tr>
            <td>Artwork</td>
            <td>- <span id="mt-info-art"><span class="mt-green">Stored</span>, <span class="mt-green">Embed</span></span></td>
        </tr>
        <tr>
            <td>Lyrics</td>
            <td>- <span id="mt-info-lyric"><span class="mt-red">Stored</span>, <span class="mt-red">Embed</span></span></td>
        </tr>
        <tr>
            <td>Tags</td>
            <td>- <span id="mt-info-tags"><span class="mt-green">Parsed</span>, <span class="mt-green">Embed</span></span></td>
        </tr>
        <tr>
            <td>Links</td>
            <td>-
                <span id="mt-dir"><a href="" target="_blank">[DIR]</a></span>, 
                <span id="mt-track-dir"><a href="" target="_blank">[Track]</a></span>, 
                <span id="mt-art-dir"><a href="" target="_blank">[Artwork]</a></span>, 
                <span id="mt-lyrics-dir"><a href="" target="_blank">[Lyrics file]</a></span>
            </td>
        </tr>
    </table>
</div>


<?php
define('MT_CONFIG_FILE', 'Index.config.php');
require_once 'Include/Config.php';



$response[trackData] = array(
    title      => 'Im Not Alone (Deadmau5 Mix)',
    album      => 'Calvin Harris',
    artist     => 'Im Not Alone (Single)',
    trackUrl   => '',
    artworkUrl => 'http://3.avatars.yandex.net/get-music-content/5560c9b4.a.1606291-1/200x200',
);

$response[tagsStream] = array(
    0 => array(
        origin  => 'remote',
        opt     => array(),
        similar => '93',
        meta    => array(
            title  => 'Im Not Alone (Deadmau5 Mix)',
            album  => 'Calvin Harris',
            artist => 'Im Not Alone (Single)',
        ),
    ),
    1 => array(
        origin  => 'remote',
        opt     => array(),
        similar => '77',
        meta    => array(
            title  => 'Im Not Alone (Deadmau5 Mix)',
            album  => 'Calvin Harris',
            artist => 'Im Not Alone',
        ),
    ),
);

$response[tags] = array(
    success => true,
    chain   => 'Alternative & Punk->Trance->Trance',
    genre   => 'Alternative & Punk',
    number  => '1',
    year    => '2013',
    bpm     => '120',
    similar => '93',
    similarStr => '65, 77, 93',
);

$response[lyrics] = array(
    success => true,
    xmlUrl  => 'xmlUrl',
    textUrl => 'textUrl',
    chars   => '1281',
    rows    => '40',
    header  => 'Calvin Harris:Im Not Alone (Deadmau5 Mix)',
    lyrics  => 'Some Lyrics Text',
);

$response[flags] = array(
    success => true,    
    track   => array(
        stored => true
    ),
    artwork => array(
        stored => true,
        embed  => true
    ),
    lyrics  => array(
        stored => false,
        embed  => false
    ),
    tags    => array(
        stored => true,
        embed  => true
    ),
);

$response[links] = array(
    path    => 'dir',
    track   => 'track',
    artwork => 'artwork',
    lyrics  => 'lyrics',

);



dbg($response);
exit;
?>