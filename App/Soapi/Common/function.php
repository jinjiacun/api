<?php
/**
 * 生成缩略图
 * @author yangzhiguo0903@163.com
 * @param string     源图绝对完整地址{带文件名及后缀名}
 * @param string     目标图绝对完整地址{带文件名及后缀名}
 * @param int        缩略图宽{0:此时目标高度不能为0，目标宽度为源图宽*(目标高度/源图高)}
 * @param int        缩略图高{0:此时目标宽度不能为0，目标高度为源图高*(目标宽度/源图宽)}
 * @param int        是否裁切{宽,高必须非0}
 * @param int/float  缩放{0:不缩放, 0<this<1:缩放到相应比例(此时宽高限制和裁切均失效)}
 * @return boolean
 */
function img2thumb($src_img, $dst_img, $width = 75, $height = 75, $cut = 0, $proportion = 0)
{
    if(!is_file($src_img))
    {
        return false;
    }
    $ot = fileext($dst_img);
    $otfunc = 'image' . ($ot == 'jpg' ? 'jpeg' : $ot);
    $srcinfo = getimagesize($src_img);
    $src_w = $srcinfo[0];
    $src_h = $srcinfo[1];
    $type  = strtolower(substr(image_type_to_extension($srcinfo[2]), 1));
    $createfun = 'imagecreatefrom' . ($type == 'jpg' ? 'jpeg' : $type);
 
    $dst_h = $height;
    $dst_w = $width;
    $x = $y = 0;
 
    /**
     * 缩略图不超过源图尺寸（前提是宽或高只有一个）
     */
    if(($width> $src_w && $height> $src_h) || ($height> $src_h && $width == 0) || ($width> $src_w && $height == 0))
    {
        $proportion = 1;
    }
    if($width> $src_w)
    {
        $dst_w = $width = $src_w;
    }
    if($height> $src_h)
    {
        $dst_h = $height = $src_h;
    }
 
    if(!$width && !$height && !$proportion)
    {
        return false;
    }
    if(!$proportion)
    {
        if($cut == 0)
        {
            if($dst_w && $dst_h)
            {
                if($dst_w/$src_w> $dst_h/$src_h)
                {
                    $dst_w = $src_w * ($dst_h / $src_h);
                    $x = 0 - ($dst_w - $width) / 2;
                }
                else
                {
                    $dst_h = $src_h * ($dst_w / $src_w);
                    $y = 0 - ($dst_h - $height) / 2;
                }
            }
            else if($dst_w xor $dst_h)
            {
                if($dst_w && !$dst_h)  //有宽无高
                {
                    $propor = $dst_w / $src_w;
                    $height = $dst_h  = $src_h * $propor;
                }
                else if(!$dst_w && $dst_h)  //有高无宽
                {
                    $propor = $dst_h / $src_h;
                    $width  = $dst_w = $src_w * $propor;
                }
            }
        }
        else
        {
            if(!$dst_h)  //裁剪时无高
            {
                $height = $dst_h = $dst_w;
            }
            if(!$dst_w)  //裁剪时无宽
            {
                $width = $dst_w = $dst_h;
            }
            $propor = min(max($dst_w / $src_w, $dst_h / $src_h), 1);
            $dst_w = (int)round($src_w * $propor);
            $dst_h = (int)round($src_h * $propor);
            $x = ($width - $dst_w) / 2;
            $y = ($height - $dst_h) / 2;
        }
    }
    else
    {
        $proportion = min($proportion, 1);
        $height = $dst_h = $src_h * $proportion;
        $width  = $dst_w = $src_w * $proportion;
    }
 
    $src = $createfun($src_img);
    $dst = imagecreatetruecolor($width ? $width : $dst_w, $height ? $height : $dst_h);
    $white = imagecolorallocate($dst, 255, 255, 255);
    imagefill($dst, 0, 0, $white);
 
    if(function_exists('imagecopyresampled'))
    {
        imagecopyresampled($dst, $src, $x, $y, 0, 0, $dst_w, $dst_h, $src_w, $src_h);
    }
    else
    {
        imagecopyresized($dst, $src, $x, $y, 0, 0, $dst_w, $dst_h, $src_w, $src_h);
    }
    $otfunc($dst, $dst_img);
    imagedestroy($dst);
    imagedestroy($src);
    return true;
}

 
function imagecreatefrombmp($file) 
{ 
global $CurrentBit, $echoMode; 
$f=fopen($file,"r"); 
$Header=fread($f,2); 
if($Header=="BM") 
{ 
$Size=freaddword($f); 
$Reserved1=freadword($f); 
$Reserved2=freadword($f); 
$FirstByteOfImage=freaddword($f); 
$SizeBITMAPINFOHEADER=freaddword($f); 
$Width=freaddword($f); 
$Height=freaddword($f); 
$biPlanes=freadword($f); 
$biBitCount=freadword($f); 
$RLECompression=freaddword($f); 
$WidthxHeight=freaddword($f); 
$biXPelsPerMeter=freaddword($f); 
$biYPelsPerMeter=freaddword($f); 
$NumberOfPalettesUsed=freaddword($f); 
$NumberOfImportantColors=freaddword($f); 
if($biBitCount<24) 
{ 
$img=imagecreate($Width,$Height); 
$Colors=pow(2,$biBitCount); 
for($p=0;$p<$Colors;$p++) 
{ 
$B=freadbyte($f); 
$G=freadbyte($f); 
$R=freadbyte($f); 
$Reserved=freadbyte($f); 
$Palette[]=imagecolorallocate($img,$R,$G,$B); 
} 
if($RLECompression==0) 
{ 
$Zbytek=(4-ceil(($Width/(8/$biBitCount)))%4)%4; 
for($y=$Height-1;$y>=0;$y--) 
{ 
$CurrentBit=0; 
for($x=0;$x<$Width;$x++) 
{ 
$C=freadbits($f,$biBitCount); 
imagesetpixel($img,$x,$y,$Palette[$C]); 
} 
if($CurrentBit!=0) {freadbyte($f);} 
for($g=0;$g<$Zbytek;$g++) 
freadbyte($f); 
} 
} 
} 
if($RLECompression==1) //$BI_RLE8 
{ 
$y=$Height; 
$pocetb=0; 
while(true) 
{ 
$y--; 
$prefix=freadbyte($f); 
$suffix=freadbyte($f); 
$pocetb+=2; 
$echoit=false; 
if($echoit)echo "Prefix: $prefix Suffix: $suffix<BR>"; 
if(($prefix==0)and($suffix==1)) break; 
if(feof($f)) break; 
while(!(($prefix==0)and($suffix==0))) 
{ 
if($prefix==0) 
{ 
$pocet=$suffix; 
$Data.=fread($f,$pocet); 
$pocetb+=$pocet; 
if($pocetb%2==1) {freadbyte($f); $pocetb++;} 
} 
if($prefix>0) 
{ 
$pocet=$prefix; 
for($r=0;$r<$pocet;$r++) 
$Data.=chr($suffix); 
} 
$prefix=freadbyte($f); 
$suffix=freadbyte($f); 
$pocetb+=2; 
if($echoit) echo "Prefix: $prefix Suffix: $suffix<BR>"; 
} 
for($x=0;$x<strlen($Data);$x++) 
{ 
imagesetpixel($img,$x,$y,$Palette[ord($Data[$x])]); 
} 
$Data=""; 
} 
} 
if($RLECompression==2) //$BI_RLE4 
{ 
$y=$Height; 
$pocetb=0; 
/*while(!feof($f)) 
echo freadbyte($f)."_".freadbyte($f)."<BR>";*/ 
while(true) 
{ 
//break; 
$y--; 
$prefix=freadbyte($f); 
$suffix=freadbyte($f); 
$pocetb+=2; 
$echoit=false; 
if($echoit)echo "Prefix: $prefix Suffix: $suffix<BR>"; 
if(($prefix==0)and($suffix==1)) break; 
if(feof($f)) break; 
while(!(($prefix==0)and($suffix==0))) 
{ 
if($prefix==0) 
{ 
$pocet=$suffix; 
$CurrentBit=0; 
for($h=0;$h<$pocet;$h++) 
$Data.=chr(freadbits($f,4)); 
if($CurrentBit!=0) freadbits($f,4); 
$pocetb+=ceil(($pocet/2)); 
if($pocetb%2==1) {freadbyte($f); $pocetb++;} 
} 
if($prefix>0) 
{ 
$pocet=$prefix; 
$i=0; 
for($r=0;$r<$pocet;$r++) 
{ 
if($i%2==0) 
{ 
$Data.=chr($suffix%16); 
} 
else 
{ 
$Data.=chr(floor($suffix/16)); 
} 
$i++; 
} 
} 
$prefix=freadbyte($f); 
$suffix=freadbyte($f); 
$pocetb+=2; 
if($echoit) echo "Prefix: $prefix Suffix: $suffix<BR>"; 
} 
for($x=0;$x<strlen($Data);$x++) 
{ 
imagesetpixel($img,$x,$y,$Palette[ord($Data[$x])]); 
} 
$Data=""; 
} 
} 
if($biBitCount==24) 
{ 
$img=imagecreatetruecolor($Width,$Height); 
$Zbytek=$Width%4; 
for($y=$Height-1;$y>=0;$y--) 
{ 
for($x=0;$x<$Width;$x++) 
{ 
$B=freadbyte($f); 
$G=freadbyte($f); 
$R=freadbyte($f); 
$color=imagecolorexact($img,$R,$G,$B); 
if($color==-1) $color=imagecolorallocate($img,$R,$G,$B); 
imagesetpixel($img,$x,$y,$color); 
} 
for($z=0;$z<$Zbytek;$z++) 
freadbyte($f); 
} 
} 
return $img; 
} 
fclose($f); 
} 
function freadbyte($f) 
{ 
return ord(fread($f,1)); 
} 
function freadword($f) 
{ 
$b1=freadbyte($f); 
$b2=freadbyte($f); 
return $b2*256+$b1; 
} 
function freaddword($f) 
{ 
$b1=freadword($f); 
$b2=freadword($f); 
return $b2*65536+$b1; 
} 


function fileext($file)
{
    return pathinfo($file, PATHINFO_EXTENSION);
}

//check is mobile from request
function is_mobile_request() 
{ 
 $_SERVER['ALL_HTTP'] = isset($_SERVER['ALL_HTTP']) ? $_SERVER['ALL_HTTP'] : ''; 
 $mobile_browser = '0'; 
 if(preg_match('/(up.browser|up.link|mmp|symbian|smartphone|midp|wap|phone|iphone|ipad|ipod|android|xoom)/i', strtolower($_SERVER['HTTP_USER_AGENT']))) 
  $mobile_browser++; 
 if((isset($_SERVER['HTTP_ACCEPT'])) and (strpos(strtolower($_SERVER['HTTP_ACCEPT']),'application/vnd.wap.xhtml+xml') !== false)) 
  $mobile_browser++; 
 if(isset($_SERVER['HTTP_X_WAP_PROFILE'])) 
  $mobile_browser++; 
 if(isset($_SERVER['HTTP_PROFILE'])) 
  $mobile_browser++; 
 $mobile_ua = strtolower(substr($_SERVER['HTTP_USER_AGENT'],0,4)); 
 $mobile_agents = array( 
    'w3c ','acs-','alav','alca','amoi','audi','avan','benq','bird','blac', 
    'blaz','brew','cell','cldc','cmd-','dang','doco','eric','hipt','inno', 
    'ipaq','java','jigs','kddi','keji','leno','lg-c','lg-d','lg-g','lge-', 
    'maui','maxo','midp','mits','mmef','mobi','mot-','moto','mwbp','nec-', 
    'newt','noki','oper','palm','pana','pant','phil','play','port','prox', 
    'qwap','sage','sams','sany','sch-','sec-','send','seri','sgh-','shar', 
    'sie-','siem','smal','smar','sony','sph-','symb','t-mo','teli','tim-', 
    'tosh','tsm-','upg1','upsi','vk-v','voda','wap-','wapa','wapi','wapp', 
    'wapr','webc','winw','winw','xda','xda-'
    ); 
 if(in_array($mobile_ua, $mobile_agents)) 
  $mobile_browser++; 
 if(strpos(strtolower($_SERVER['ALL_HTTP']), 'operamini') !== false) 
  $mobile_browser++; 
 // Pre-final check to reset everything if the user is on Windows 
 if(strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'windows') !== false) 
  $mobile_browser=0; 
 // But WP7 is also Windows, with a slightly different characteristic 
 if(strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'windows phone') !== false) 
  $mobile_browser++; 
 if($mobile_browser>0) 
  return true; 
 else
  return false;
}

function retrieve($url) 
{ 
preg_match('/\/([^\/]+\.[a-z]+)[^\/]*$/',$url,$match); 
return $match[1]; 
} 

function to_utf8($in) 
{ 
	if (is_array($in)) { 
	foreach ($in as $key => $value) 
	{ 
	$out[$this->to_utf8($key)] = $this->to_utf8($value); 
	} 
	} 
	elseif(is_string($in)) 
	{ 
	if(mb_detect_encoding($in) != "UTF-8") 
	return utf8_encode($in); 
	else 
	return $in; 
	} 
	else 
	{ 
	return $in; 
	} 
	return $out; 
}

function getOS()
{
	$agent = strtolower($_SERVER["HTTP_USER_AGENT"]);
	if(strpos($agent, "windows nt")) {
		$platform = 'windows';
	} elseif(strpos($agent, 'macintosh')) {
		$platform = 'mac';
	} elseif(strpos($agent, 'ipod')) {
		$platform = 'ipod';
	} elseif(strpos($agent, 'ipad')) {
		$platform = 'ipad';
	} elseif(strpos($agent, 'iphone')) {
		$platform = 'iphone';
	} elseif (strpos($agent, 'android')) {
		$platform = 'android';
	} elseif(strpos($agent, 'unix')) {
		$platform = 'unix';
	} elseif(strpos($agent, 'linux')) {
		$platform = 'linux';
	} else {
		$platform = 'other';
	}
	return $platform;
}
