<?php

    if (!isset($_GET['user'])) {
        if (!isset($_GET['hashtag'])) {
            exit('Not a valid RSS feed. You didn\'nt provide an Instagram user or hashtag. Send one via a GET variable. Example .../instarss.php?user=snoopdogg');
        }
    }

    if (isset($_GET['user']) && isset($_GET['hashtag'])) {
        exit('Don\'t request both user and hashtag. Request one or the other.');
    }

    header('Content-Type: text/xml; charset=utf-8');

    if (isset($_GET['user'])) {
        $html = file_get_contents('http://instagram.com/'.$_GET['user'].'/');
    }
    if (isset($_GET['hashtag'])) {
        $html = file_get_contents('http://instagram.com/explore/tags/'.$_GET['hashtag'].'/');
    }
    $html = strstr($html, '{"country_code');
    $html = strstr($html, '</script>', true);
    $html = substr($html, 0, -1);

    // for debugging... sigh........
    // echo $html;

    $data = json_decode($html);

    // more debugging... 
    // print_r($data->entry_data->ProfilePage[0]->user->media->nodes);

    if (isset($_GET['user'])) {
        $nodes = $data->entry_data->ProfilePage[0]->user->media->nodes;
    }
    if (isset($_GET['hashtag'])) {
        $nodes = $data->entry_data->TagPage[0]->tag->media->nodes;
    }

    $rss_feed = '<?xml version="1.0" encoding="UTF-8"?><rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom" xmlns:dc="http://purl.org/dc/elements/1.1/"><channel>';

    if (isset($_GET['user'])) {
        $rss_feed .= '<title>'.$_GET['user'].'\'s Instagram Feed</title><atom:link href="http://'.$_SERVER['HTTP_HOST'].$_SERVER["REQUEST_URI"].'" rel="self" type="application/rss+xml" /><link>http://instagram.com/'.$_GET['user'].'</link><description>'.$_GET['user'].'\'s Instagram Feed</description>';
    }

    if (isset($_GET['hashtag'])) {
        $rss_feed .= '<title>Photos tagged with: '.$_GET['hashtag'].' on Instagram</title><atom:link href="http://'.$_SERVER['HTTP_HOST'].$_SERVER["REQUEST_URI"].'" rel="self" type="application/rss+xml" /><link>http://instagram.com/explore/tags/'.$_GET['hashtag'].'</link><description>Photos tagged with: '.$_GET['hashtag'].' on Instagram</description>';
    }

    foreach($nodes as $node) {

        $rss_feed .= '<item><title>';

        if(isset($node->caption) && $node->caption != '') {
            $rss_feed .= htmlspecialchars($node->caption, ENT_QUOTES);
        } else {
            $rss_feed .= 'photo';
        }

        // pubdate format could also be: "D, d M Y H:i:s T"
        $rss_feed .= '</title><link>https://instagram.com/p/'.$node->code.'/</link><pubDate>'.date("r", $node->date).'</pubDate>';

        if (isset($_GET['user'])) {
            $rss_feed .= '<dc:creator><![CDATA['.$_GET['user'].']]></dc:creator>';
        }

        $rss_feed .= '<description><![CDATA[<img src="'.$node->display_src.'" />]]></description><guid>https://instagram.com/p/'.$node->code.'/</guid></item>';

    } // foreach "node" (photo)

    $rss_feed .= '</channel></rss>';

    echo $rss_feed;

?>
