<?php
define('MT_CONFIG_FILE', 'Index.config.php');

require_once 'Include/Config.php';




$trackData = array(
    /**/
    'title'      => 'Кокать лампочки',
    'album'      => 'ф',
    'artist'     => 'Ландыши',
    'trackUrl'   => 'http://s8.pleer.com/0042fdbee17d4b9d6f20cae4c501ab68ceece848791c693523a6873b78e93ba17cfa4d332c325369afb37342d48b3ea4eb540cc8e66bcffa1cd747e264ef33dad9c87838203d134557fafa558f0def0b3605c1/1364103b5b.mp3',
    'artworkUrl' => '',

    /** /
    'title'      => 'Lost In Hollywood',
    'album'      => 'Mezmerize',
    'artist'     => 'System Of A Down',
    'trackUrl'   => 'http://elisto14f.mds.yandex.net/get-mp3/0bb3b289942bd2b60ad736bb582174bc/5109aaa4abe97/music/13/2/data-0.2:28295683085:4547812?track-id=648930&play=false&experiments=%7B%22feedTracksByGenre%22%3A%22default%22%2C%22userFeed%22%3A%22feed12%22%2C%22feedLongMemory%22%3A%22default%22%2C%22feedRareArtist%22%3A%22default%22%2C%22feedMissedTracksByArtist%22%3A%22default%22%2C%22feedArtistByFriends%22%3A%22default%22%2C%22similarities%22%3A%22default%22%2C%22genreRadio%22%3A%22matrixnet-default%22%2C%22recommendedArtists%22%3A%22ichwill_similar_artists%22%2C%22recommendedTracks%22%3A%22recommended_tracks_by_artist_from_history%22%2C%22recommendedAlbumsOfFavoriteGenre%22%3A%22recent%22%2C%22recommendedSimilarArtists%22%3A%22default%22%2C%22recommendedArtistsWithArtistsFromHistory%22%3A%22force_recent%22%2C%22adv%22%3A%22a%22%2C%22loserArtistsWithArtists%22%3A%22off%22%2C%22ny2015%22%3A%22no%22%7D&from=web-artist_tracks-track-track-main&albumId=73482',
    'artworkUrl' => 'http://1.avatars.yandex.net/get-music-content/53c6be0e.a.73442-1/200x200',

    /** /
    'title'      => 'Hey Now',
    'album'      => 'If You Wait',
    'artist'     => 'London Grammar',
    'trackUrl'   => 'http://elisto04d.music.yandex.ru/get-mp3/07bf38af482ec38b2c6dce5f51fa7626/506963ade3b08/34/data-0.12:38452698079:4973504?track-id=14671865&play=false&experiments=%7B%22similarities%22%3A%22default%22%2C%22genreRadio%22%3A%22new-ichwill%22%2C%22newMusic%22%3A%22no%22%2C%22recommendedArtists%22%3A%22ichwill_similar_artists%22%2C%22recommendedTracks%22%3A%22recommended_tracks_by_artist_from_history%22%2C%22recommendedAlbumsOfFavoriteGenre%22%3A%22recent%22%2C%22recommendedSimilarArtists%22%3A%22default%22%2C%22recommendedArtistsWithArtistsFromHistory%22%3A%22default%22%2C%22adv%22%3A%22a%22%2C%22myMusicButton%22%3A%22yes%22%7D&from=web-artist_albums-album-track-fridge&albumId=1606291',
    'artworkUrl' => 'http://3.avatars.yandex.net/get-music-content/5560c9b4.a.1606291-1/200x200',

    /** /
    'trackUrl'   => 'http://t1-2.p-cdn.com/access/?version=4&lid=946783457&token=cqf%2Bh%2BdUKV1%2BM4GARZanyUV%2BXk6ZfdmDfSeUE90%2ByDaT%2FaFexRhOhSxZD0qA28G351vbJgiKhv3303dABMMyvwZZSC0sj5MUMUTQujGHkoU29CGoniWCTX9zNILTE7puwclRvXOpA3nDGD1eWpbC0wtEIk6z1onkPn7EFtt2gPXZ7dvyfMZcPKwPF9e2nmzU3MFeS6s4TieSNbbWz1vsgXjjwlMQQFZ0jUOPbHpbD3h7h9Ih55bRksx1BD16Kab3Zb89Xcr%2BSRbnGl41NsbhhbJiD8uYI92pOoliu9KNK5XuyE1ffDJqHqdDHLlONo1XAoJIYPMhmK1ht9GHymUsZJHv8U0jR7ZZ%2FRl17DSineIzMB0%2FaIfCaEj0ms8cAjIS',
    'artworkUrl' => 'http://cont-dc6-2.pandora.com/images/public/amz/2/6/1/5/800015162_500W_500H.jpg',
    'artworkUrl' => 'http://www.queness.com/resources/images/png/apple_ex.png',
    'artworkUrl' => 'http://www.newyorker.com/wp-content/uploads/2012/12/Gif-1.gif',
    /**/
);
$options = array();


$musicTug = new MusicTug($trackData, $options);
$musicTug->init();
// $l[] = $musicTug->getTagsStream();
// $l[] = $musicTug->getLyricsStream();
// $l[] = $musicTug->getArtworkStream();
// $l[] = $musicTug->getTrackStream();
// dbg($l);


// $b = new MusicTugApi();
// $b->route('here?');
// dbg(MusicTugHelper::getConfig());