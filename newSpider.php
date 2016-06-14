<?php
/**
 * 爬虫程序 -- 原型
 *
 * 从给定的url获取html内容
 * 
 * @param string $url 
 * @return string 
 */
function _getUrlContent($url) {
    $handle = fopen($url, "r");
    if ($handle) {
        $content = stream_get_contents($handle, 1024 * 1024);
        return $content;
    } else {
        return false;
    } 
} 
/**
 * 从html内容中筛选链接
 * 
 * @param string $web_content 
 * @return array 
 */
function filter($arr) {
	$linkTilte = array();
	foreach($arr as $news) {
		$news = trim($news);
		$items = explode("\"", $news);
		$newlink = $items[1];
		$title = str_replace(array(">", "</a"), "", $items[4]);
		$linkTitle[] = array($newlink, $title);
	}
	return $linkTitle;
} 

function getCurImage($filename, $title) {
	$arr = array();
	$cmd = "cat $filename|grep \"<img src=\" |grep $title";
	exec($cmd, $arr);
	$simage = trim($arr[0]);
	$items = explode("\"", $simage);
	return $items[1];
}
function getLinkTitle($filename, $type) {
    exec("cat $filename |grep \"http://voice.hupu.com/$type/\"|grep -v class|grep \"</a>\"|grep html", $arr);
    $ret = filter($arr);
    return $ret;
}
/**
 * 爬虫
 * 
 * @param string $url 
 * @return array 
 */
function crawler($url, &$retSourceArr, $type) {
    $filename = "tmpfile.txt";
    $content = _getUrlContent($url);
    $myfile = fopen($filename, "w") or die("Unable to open file!");
    fwrite($myfile, $content);
    fclose($myfile);
    $ret = getLinkTitle($filename, $type);
    foreach($ret as $item) {
        $curlink = $item[0];
        $curtitle = $item[1];
	
	sleep(0.1);
	echo "-";
    	$content = _getUrlContent($curlink);

        $myfile = fopen($filename, "w") or die("Unable to open file!");
        fwrite($myfile, $content);
        fclose($myfile);
        
        $imageLink = getCurImage($filename, $curtitle);
        
        $tmpArr = array('newlink' => $curlink, 'title' => $curtitle, 'imagelink' => $imageLink);
	$retSourceArr[] = $tmpArr;
    }
    echo "\n";
} 
/**
 * 测试用主程序
 */
function main($type, $num, $outFile) {
    $resfile = fopen($outFile, "w") or die("Unable to open file!");
    for($i=1; $i <= $num; $i++) {
    	$current_url = "http://voice.hupu.com/$type/$i"; //初始url
    	$resArr = array();
    	crawler($current_url, $resArr, $type);
    	foreach($resArr as $res) {
		$s = $res['newlink'] . "\t" . $res['title'] . "\t" . $res['imagelink'] . "\n";
		echo $s;
		fwrite($resfile, $s);
    	}
    }
    fclose($resfile);
}
$type = $argv[1];
$num = $argv[2];
$outFile = $argv[3];
main($type, $num, $outFile);
 
?>
