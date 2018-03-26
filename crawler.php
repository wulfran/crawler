<?php
//error_reporting(E_ALL);
//ini_set('display_errors', 1);
$start = microtime(true);
function crawl($url,$max_deep = 10,$seen_links = array(),$deep = 1){
    if ((stristr($url, 'http') == FALSE) || (stristr($url,'https://'))){
        $url = 'http://' . $url;
    }
    $domain = parse_url($url,PHP_URL_HOST);
    $path = parse_url($url, PHP_URL_PATH);

    $link = 'http://' . $domain . $path;

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
    @$doc->loadHTMLFile($link);
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

    //cleaning the array
    $linksToDo = array_unique($linksToDo);
    $linksToDo = array_diff($linksToDo, $seen);

    //getting the SEO data:
    $titleObject = $doc->getElementsByTagName('title');
    $title = $titleObject->item(0)->nodeValue;

    $descObject = $doc->getElementsByTagName('meta');
    foreach($descObject as $object){
        if($object->getAttribute('name') == 'description'){
            $desc = $object->getAttribute('content');
        } elseif ($object->getAttribute('name') == 'keywords'){
            $keywords = $object->getAttribute('content');
        }
    }

    if(!isset($title) || $title == NULL){ $title[1]='No title on the page';}
    if(!isset($desc) || $desc == NULL){ $desc[1]='No description on the page';}
    if(!isset($keywords) || $keywords == NULL){ $keywords='No keywords on the page';}
    // display the results
    echo 'Testing: <strong>'.$url.'</strong><br />';
    echo 'Title: <strong>'.$title.'</strong><br/>';
    echo 'Description: <strong>'.$desc.'</strong><br/>';
    echo 'Keywords: <strong>'.$keywords.'</strong><br/><br/>';
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
$max_deep = intval($_POST['deep']);
crawl($domain, $max_deep);
echo 'Script executed in ' . number_format((microtime(true) - $start),2) . ' sec';