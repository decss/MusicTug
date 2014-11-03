<?php
define('MT_CONFIG_FILE', 'Index.config.php');
require_once 'Include/Config.php';




$trackData = array(
    'title'      => 'Hey Now',
    'album'      => 'If You Wait',
    'artist'     => 'London Grammar',
    'trackUrl'   => 'http://elisto04d.music.yandex.ru/get-mp3/07bf38af482ec38b2c6dce5f51fa7626/506963ade3b08/34/data-0.12:38452698079:4973504?track-id=14671865&play=false&experiments=%7B%22similarities%22%3A%22default%22%2C%22genreRadio%22%3A%22new-ichwill%22%2C%22newMusic%22%3A%22no%22%2C%22recommendedArtists%22%3A%22ichwill_similar_artists%22%2C%22recommendedTracks%22%3A%22recommended_tracks_by_artist_from_history%22%2C%22recommendedAlbumsOfFavoriteGenre%22%3A%22recent%22%2C%22recommendedSimilarArtists%22%3A%22default%22%2C%22recommendedArtistsWithArtistsFromHistory%22%3A%22default%22%2C%22adv%22%3A%22a%22%2C%22myMusicButton%22%3A%22yes%22%7D&from=web-artist_albums-album-track-fridge&albumId=1606291',
    'artworkUrl' => 'http://3.avatars.yandex.net/get-music-content/5560c9b4.a.1606291-1/200x200',
    
    // 'trackUrl'   => 'http://t1-2.p-cdn.com/access/?version=4&lid=946783457&token=cqf%2Bh%2BdUKV1%2BM4GARZanyUV%2BXk6ZfdmDfSeUE90%2ByDaT%2FaFexRhOhSxZD0qA28G351vbJgiKhv3303dABMMyvwZZSC0sj5MUMUTQujGHkoU29CGoniWCTX9zNILTE7puwclRvXOpA3nDGD1eWpbC0wtEIk6z1onkPn7EFtt2gPXZ7dvyfMZcPKwPF9e2nmzU3MFeS6s4TieSNbbWz1vsgXjjwlMQQFZ0jUOPbHpbD3h7h9Ih55bRksx1BD16Kab3Zb89Xcr%2BSRbnGl41NsbhhbJiD8uYI92pOoliu9KNK5XuyE1ffDJqHqdDHLlONo1XAoJIYPMhmK1ht9GHymUsZJHv8U0jR7ZZ%2FRl17DSineIzMB0%2FaIfCaEj0ms8cAjIS',
    // 'artworkUrl' => 'http://cont-dc6-2.pandora.com/images/public/amz/2/6/1/5/800015162_500W_500H.jpg',
    // 'artworkUrl' => 'http://www.queness.com/resources/images/png/apple_ex.png',
    // 'artworkUrl' => 'http://www.newyorker.com/wp-content/uploads/2012/12/Gif-1.gif',

    // 'title'      => 'Confusion (Instrumental)',
    // 'title'      => 'Confusion',
    // 'artist'     => 'New Order',

    
);

$options = array();

// $fi = new finfo(FILEINFO_MIME,'/usr/share/file/magic');
// $mime_type = $fi->buffer(file_get_contents($file));


$musicTug = new MusicTug($trackData, $options);

$musicTug->init();

// $l = $musicTug->getTagsStream();
// $l = $musicTug->getLyricsStream();
// $l = $musicTug->getArtworkStream();
// $l = $musicTug->getTrackStream();
// $l[file] = null;

// dbg($l);

// $b = new MusicTugApi();
// $b->route('here?');
// dbg(MusicTugHelper::getConfig());