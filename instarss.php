<?php

    if (!isset($_GET['user'])) {
        exit('Not a valid RSS feed. You didn\'nt provide an Instagram user. Send one via a GET variable. Example .../instarss.php?user=snoopdogg');
    }

    header('Content-Type: text/xml; charset=utf-8');

    $html = file_get_contents('http://instagram.com/'.$_GET['user'].'/');
    $html = strstr($html, '{"static_root');
    $html = strstr($html, '</script>', true);
    //$html = substr($html,0,-6);
    $html = substr($html, 0, -1);

    $data = json_decode($html);

    $rss_feed = '<?xml version="1.0" encoding="UTF-8"?><rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom" xmlns:dc="http://purl.org/dc/elements/1.1/"><channel>';
    $rss_feed .= '<title>'.$_GET['user'].'\'s Instagram Feed</title><atom:link href="http://'.$_SERVER['HTTP_HOST'].$_SERVER["REQUEST_URI"].'" rel="self" type="application/rss+xml" /><link>http://instagram.com/'.$_GET['user'].'</link><description>'.$_GET['user'].'\'s Instagram Feed</description>';

    foreach($data->entry_data->UserProfile[0]->userMedia as $user_media) {

        $rss_feed .= '<item><title>';

        if(isset($user_media->caption->text) && $user_media->caption->text != '') {
            $rss_feed .= htmlspecialchars($user_media->caption->text, ENT_QUOTES, ENT_HTML5);
        } else {
            $rss_feed .= 'photo';
        }

        // pubdate format could also be: "D, d M Y H:i:s T"
        $rss_feed .= '</title><link>'.$user_media->link.'</link><pubDate>'.date("r", $user_media->created_time).'</pubDate><dc:creator><![CDATA[ '.$_GET['user'].' ]]></dc:creator><description><![CDATA[<img src="'.$user_media->images->standard_resolution->url.'" />]]></description><guid>'.$user_media->link.'</guid></item>';

    } // foreach userMedia (photo)

    $rss_feed .= '</channel></rss>';

    echo $rss_feed;

?>
