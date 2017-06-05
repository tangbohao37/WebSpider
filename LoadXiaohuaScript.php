<?php
include("simple_html_dom.php");

date_default_timezone_set('prc');
$math='div.left dl';
GetContent($math);

//时间判断。   网页更新时间<上次抓取时间  返回false
function checkTime($webTime){
    $lastRefreshTime=date("Y-m-d H:i:s",strtotime("-1 day"));
    if($webTime>$lastRefreshTime){
     return true;
    }else{
     return false;
    }
}

function GetContent($math){
    $html=file_get_html('http://www.mahua.com/');// 提取title  传链接地址
//    $con=new mysqli('120.27.27.97','gtwifi','!clxt2016','gtwifi');
//    if(mysqli_connect_errno()){
//        die('链接失败').mysqli_connect_errno();
//    }
    $n=0;
    foreach($html->find($math) as $e){
        $a=$e->find('span.joke-title a',0); //获取 a标签
        $title=$a->innertext;
        echo $title;
        $link=$a->href;//获取内页链接
        if($link===false){  //无法获取则跳过该条新闻
            continue;
        }
        $webTime=get_time($link);
        if(!checkTime($webTime)){
            $html->clear();
            exit();
        }
        //提取图片
        echo $e->find('dd.content img',0);
        $img_url=$e->find('dd.content img',0)->mahuagifimg;//寻找 GIF标签 地址
        if($img_url==false){
            $img_url=$e->find('dd.content img',0)->mahuaimg;//寻找img标签 地址
        }
        if($img_url==false){
            $img_url=$e->find('dd.content img',0)->src;
        }
        if($img_url!=false){
            //存储图片
            $img_Name=saveImage($img_url);
            if($img_Name===0){
                continue;
            }
        }else{
            continue;
        }
        echo '<hr>';
//        $sql = "INSERT INTO wifi_xiaohua (id,status,cheid,uptime,title,laiyuan,content,img) VALUES (NULL,0,0,'".date("Ymd")."','".$title."','xiaohua','','".$img_Name."');";
//        mysqli_query($con, "set names 'utf8'");
//        $result = mysqli_query($con, $sql);
//        if($result){
//            $n++;
//        }
    }
    $html->clear();
//    $con->close();
//    echo '###'.date("Y-m-d H:i:s").'成功更新'.$n.'条笑话 #### ';
//	echo "\n";
    
}

function get_time($url){
//提取内页
    $html= file_get_html($url);
    $time=$html->find('p.joke-uname span',0)->innertext;
//    $time=$time->innertext();
//    print_r('摘要'.$time);//打印摘要
    $html->clear();
    return  $time;
}

function download_img($url){
    $curlobj = curl_init();
    curl_setopt($curlobj, CURLOPT_URL, $url);
    curl_setopt($curlobj, CURLOPT_HEADER, 0);
    curl_setopt($curlobj, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curlobj, CURLOPT_TIMEOUT, 300); // times out after 300s
    $arr= explode('/',$url);
    $imgName=$arr[count($arr)-1];
    $outfile = fopen('upload/'.$imgName, 'wb');//保存到本地的文件名
    curl_setopt($curlobj, CURLOPT_FILE, $outfile);
    curl_exec($curlobj);
    fclose($outfile);
    if(!curl_error($curlobj)){
        curl_close($curlobj);
        return 'upload/'.$imgName;
    }
    else{
        curl_close($curlobj);
        return 0;
    }
}

function saveImage($path) {
    $arr= explode('/',$path);
    $imgName=$arr[count($arr)-1];
    $ch = curl_init ($path);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_BINARYTRANSFER,1);
    $img = curl_exec ($ch);
    curl_close ($ch);
    $fp = fopen('/web/wifi/Upload/'.$imgName,'w');
    if(fwrite($fp, $img)!==0){
        fclose($fp);
        return 'Upload/'.$imgName;
    }
    else{
        fclose($fp);
        return false;
    }
}

?>
