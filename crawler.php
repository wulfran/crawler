<?php
//error_reporting(E_ALL);
//ini_set('display_errors', 1);
$start = microtime(true);
function crawl($url,$max_deep = 10,$seen_links = array(),$deep = 1, &$page_info = array()){
    if ((stristr($url, 'http') == FALSE) && (stristr($url,'https://') == FALSE)){
        $url = 'http://' . $url;
    }
    $domain = parse_url($url,PHP_URL_HOST);
    $path = parse_url($url, PHP_URL_PATH);
    $current_link = 'http://' . $domain . $path;

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
    @$doc->loadHTMLFile($current_link);
    $links = $doc->getElementsByTagName('a');

    //creating a temporary link array
    foreach ($links as $element){
        if($element->getAttribute('href') != '/') {
            array_push($tmp_links, $element->getAttribute('href'));
            $tmp_links = str_replace('../', '', $tmp_links);
        }
    }
    $tmp_links = array_unique($tmp_links);	//cleaning the array

    //setting an array for links to visit
    foreach($tmp_links as $link){
        //if href is an proper url:
        if(!filter_var($link,FILTER_VALIDATE_URL)){
            if(!stristr($link,'http') || !stristr($link,'https://')){
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
    $title = trim($titleObject->item(0)->nodeValue);
    $descObject = $doc->getElementsByTagName('meta');
    foreach($descObject as $object){
        if($object->getAttribute('name') == 'description'){
            $desc = trim($object->getAttribute('content'));
        } elseif ($object->getAttribute('name') == 'keywords'){
            $keywords = trim($object->getAttribute('content'));
        }
    }
    if(!isset($title) || $title == NULL){ $title[1]='No title on the page';}
    if(!isset($desc) || $desc == NULL){ $desc[1]='No description on the page';}
    if(!isset($keywords) || $keywords == NULL){ $keywords='No keywords on the page';}

    //setting array with the results:
    $page_info[] = [
        'url' => $url,
        'title' => $title,
        'desc' => $desc,
        'keywords' => $keywords
    ];
    if($linksToDo == NULL){
        return $page_info;
    }
    //crawling the 1st unseen link form an array
    $deep++;
    if($deep <= $max_deep) {
        crawl(reset($linksToDo),$max_deep, $seen, $deep,$page_info);
    } else {
        return $page_info;
    }
    return $page_info;
}
$domain = $_POST['url'];
$max_deep = intval($_POST['deep']);
$data = crawl($domain, $max_deep);

$data[] = [
    'executionTime' => (microtime(true) - $start)
];
header('Content-type: application/json');
echo json_encode( $data );