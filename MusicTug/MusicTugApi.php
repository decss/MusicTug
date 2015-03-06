<?php
/**
 * MusicTug helper class
 */
class MusicTugApi
{

    function route($action, $data = null)
    {
        if ($action) {
            $jsonAnswer['answer'] = 'yes';
            MusicTugHelper::jsonResponse('success', $jsonAnswer);
        }
    }

}