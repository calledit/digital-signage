<?php

function ProgressUpdate($ProgressLine){
	//error_log('ffmpeg: '.$ProgressLine);
	echo('ffmpeg: '.$ProgressLine);
    flush();
}

if(isset($_GET['convert'])){
	$InFilename = $_GET['convert'];
	$Outfile = "/opt/converting_files/".$InFilename.'.mp4';
	$Infile = '/opt/uploaded_raw_files/'. $InFilename;
	$cleaned_name = preg_replace('[^a-z0-9A-Z_.]', '_', $InFilename);
	if(rename($Infile, '/opt/uploaded_raw_files/'.$cleaned_name)){
		$InFilename = $cleaned_name;
		$Infile = '/opt/uploaded_raw_files/'. $InFilename;
	}

	//convertMovie($Infile, $Outfile, 'ProgressUpdate');

	//rescale to 1080p
	runCom('/usr/bin/ffmpeg -threads 12 -y -i '.escapeshellarg($Infile).' -vcodec h264 -vf scale=1920:1080 -acodec libfdk_aac -ab 128k -movflags faststart '.escapeshellarg($Outfile).'', true, $InFilename.'.log');
	//runCom('/usr/bin/ffmpeg -threads 12 -y -i '.escapeshellarg($Infile).' -vcodec h264 -b:v 1m -acodec libfdk_aac -ab 128k -movflags faststart '.escapeshellarg($Outfile).'', true, $InFilename.'.log');

	//passthru('/usr/bin/ffmpeg -i "'.$Infile.'" -y -vcodec h264 -b:v 400k -acodec libfaac -ab 32k -movflags faststart "'.$Outfile.'" 2>&1');
}

function runCom($CommandText, $Paralel, $ident){
	$AsUserCommand = "";
    if($Paralel){
        $AsUserCommand .= "nohup ";
    }
    $AsUserCommand .= $CommandText;
    $AsUserCommand .= " > /dev/null 2> /tmp/conversionlog/".$ident;
    if($Paralel){
        $AsUserCommand .= " < /dev/null & ";
    }
    //$FullCommand = "/bin/bash -c '".$AsUserCommand."' 2>&1";
	//var_dump($AsUserCommand);
	//exit();
    //if($Paralel){
        //$FullCommand .= " > /dev/null";
    //}
	 $FullCommand = $AsUserCommand;
    exec($FullCommand, $Output, $OutRes);
    return(array('stdout' => $Output, 'result' => $OutRes));
}

/*
function convertMovie($Infile, $Outfile, $OnUpdate){
	$ProcessReader = popen('/usr/bin/ffmpeg -i '.escapeshellarg($Infile).' -vcodec h264 -b:v 400k -acodec libfaac -ab 32k -movflags faststart '.escapeshellarg($Outfile).' 2>&1', "r");
	while(!feof($ProcessReader)){
		$OnUpdate(getline($ProcessReader));
	}
	fclose($ProcessReader);
}
 */
/*
if(isset($_GET['videofile'])){
    $vfname = urldecode(strval($_GET['videofile']));
    if(substr($vfname, 0, $mstartlen) === $moststart){
        $vfname = substr($vfname, $mstartlen);
        $CleanedName = realpath($internalfoler.'/'.$vfname);
        if(!$CleanedName)
            throw new Exception('the reuested file is not real');
        $file2convertInfo = pathinfo($CleanedName);
        if(strpos($file2convertInfo['dirname'], $internalfoler) === 0){
            $ProcessReader = popen("/usr/bin/2mp4 ".escapeshellarg($CleanedName)." 2>&1", "r");
            while(!feof($ProcessReader)){
                echo(FilterOut(getline($ProcessReader)));
                flush();
            }
            fclose($ProcessReader);
            if($ConversionStared){
                echo("          </table>\n");
            }
            //passthru("/usr/bin/2mp4 ".escapeshellarg($CleanedName)." 2>&1");
        }else{
            echo("Unauthorised File\n");
        }
    }
}
*/

function getline($fp){
    $result = "";
    while(!feof($fp)){
        $tmp = fgetc( $fp );
        $result .= $tmp;
        if($tmp == "\n" || $tmp == "\r"){
            return($result);
        }
    }
    return($result);
}

function FilterOut($inputLine){
    global $ConversionStared, $LineNum;
    $LineNum += 1;
    if(substr($inputLine,-1) == "\n"){
        if($LineNum == 1){
            return('<h3>'.$inputLine.'</h3><hr>');
        }
        return($inputLine.'<br/>');
    }
    if(!$ConversionStared){
        $ConversionStared = true;
        echo("          </div>\n");
        echo("          <table class=\"upsidedown\">\n");

    }
    return('<tr><td>'.$inputLine.'</td></tr>');
}
