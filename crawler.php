<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

function crawl($url,$max_deep = 10,$seen_links = array(),$deep = 1){
    $domain = parse_url($url, 1);
    if($domain == NULL){
        $domain = parse_url($url, -1);
        $domain = str_replace('www.', '', $domain['path']);
        $domain = 'http://' . $domain;
    }
    //var_dump($domain);
    //die();
    $b = file_get_contents($domain); 	//getting the page content
    //check if seen array has been defined
    if(!isset($seen) || $seen == NULL){
        $seen = array();				//seen urls
    }
    $seen = $seen_links;
    array_push($seen,$url);
    $tmp_links = array();			//tmp list of <a> objects
    $linksToDo = array();			//links to visit
    //$out_links = array();			//links leading outside


    //getting all the <a href=".*"> values
    $doc = new DOMDocument('1.0');
    @$doc->loadHTMLFile($url);
    $links = $doc->getElementsByTagName('a');

    //creating a temporary link array
    foreach ($links as $element){
        array_push($tmp_links, $element->getAttribute('href'));
        $tmp_links = str_replace('../','',$tmp_links);
    }
    $tmp_links = array_unique($tmp_links);	//cleaning the array

    //setting an array for links to visit
    foreach($tmp_links as $link){
        //if href is an proper url:
        if(!filter_var($link,FILTER_VALIDATE_URL)){
            if(stristr($link,'http') || stristr($link,'https://')){
                //no action needed
            }else{
                //setting the link to a proper form
                $link = ltrim($link,'/');
                $domain = rtrim($domain,'/');
                if($link != '/'){$domain.='/';}
                $link = 'http://'.$domain.$link;
                if($link != $url){
                    array_push($linksToDo,$link);
                }
            }
        }else{
            $domain = rtrim($domain,'/');
            if(strpos ($link,$domain)){
                if(!stristr($link,'mailto')){
                    if($link != $url){
                        array_push($linksToDo,$link);
                    }
                }
            }
        }
    }

    //cleanign the array
    $linksToDo = array_unique($linksToDo);
    $linksToDo = array_diff($linksToDo, $seen);

    //getting the SEO data:
    preg_match('#<title>(.*)</title>#Umsi',$b,$title);
    if(!isset($title) || $title == NULL){ $title[1]='No title on the page';}
    preg_match('#name="description" content="(.*)"#',$b,$desc);
    if(!isset($desc) || $desc == NULL){ $desc[1]='No description on the page';}
    preg_match('#name="keywords" content="(.*)"#',$b,$keywords);
    if(!isset($keywords) || $keywords == NULL){ $keywords[1]='No keywords on the page';}
    // display the results
    echo 'Testing: <strong>'.$url.'</strong><br />';
    echo 'Title: <strong>'.$title[1].'</strong><br/>';
    echo 'Description: <strong>'.$desc[1].'</strong><br/>';
    echo 'Keywords: <strong>'.$keywords[1].'</strong><br/><br/>';
    if($linksToDo == NULL){
        return;
    }
    //crawling the 1st unseen link form an array
    $deep++;
    if($deep <= $max_deep) {
        crawl(reset($linksToDo),$max_deep, $seen, $deep);
    } else {
        return;
    }
}
$domain = $_POST['url'];
$max_deep = $_POST['deep'];
crawl($domain, $max_deep);
