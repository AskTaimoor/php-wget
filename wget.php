<?php
set_time_limit(0);
ignore_user_abort(true);

if($argc<4)die('Invalid Arguments!'.PHP_EOL.'php -f wget.php http://www.someweb.com/bigfile.zip file_size'.PHP_EOL);

$url	= $argv[1];
$to_file = $argv[2];
$file_size = $argv[3];


/**
 * Returns the size of a file without downloading it, or -1 if the file
 * size could not be determined.
 *
 * @param $url - The location of the remote file to download. Cannot
 * be null or empty.
 *
 * @return The size of the file referenced by $url, or -1 if the size
 * could not be determined.
 */
function curl_get_file_size( $url ) {
  // Assume failure.
  $result = -1;

  $curl = curl_init( $url );

  // Issue a HEAD request and follow any redirects.
  curl_setopt( $curl, CURLOPT_NOBODY, true );
  curl_setopt( $curl, CURLOPT_HEADER, true );
  curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true );
  curl_setopt( $curl, CURLOPT_FOLLOWLOCATION, true );
  curl_setopt( $curl, CURLOPT_USERAGENT, get_user_agent_string() );

  $data = curl_exec( $curl );
  curl_close( $curl );

  if( $data ) {
    $content_length = "unknown";
    $status = "unknown";

    if( preg_match( "/^HTTP\/1\.[01] (\d\d\d)/", $data, $matches ) ) {
      $status = (int)$matches[1];
    }

    if( preg_match( "/Content-Length: (\d+)/", $data, $matches ) ) {
      $content_length = (int)$matches[1];
    }

    // http://en.wikipedia.org/wiki/List_of_HTTP_status_codes
    if( $status == 200 || ($status > 300 && $status <= 308) ) {
      $result = $content_length;
    }
  }

  return $result;
}

function get_user_agent_string(){
return 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/43.0.2357.124 Safari/537.36';
}



$max_file_chunk=1024 * 1024 * 64; /* 128 MB */

$file_chunk_count=$file_size/$max_file_chunk;

echo 'File Size: '.number_format($file_size).PHP_EOL;

$chunk_count_int=number_format($file_chunk_count);

echo 'Total Chunks To Download: '.number_format($chunk_count_int).PHP_EOL;

$is_downloaded=false;
$ctr=1;

while(!$is_downloaded){

$ch=curl_init($url);
$opt=array();
if(is_file($to_file))
{
	$sz=filesize($to_file);
clearstatcache();
	if($file_size<=$sz){
	echo PHP_EOL.'Download Completed.'.PHP_EOL;
	$is_downloaded=true;
	continue;
	}
$file_remaining_bytes_to_be_downloaded=$file_size-$sz;

$fetch_chunk_part=$max_file_chunk;

if($file_remaining_bytes_to_be_downloaded<$fetch_chunk_part){
$fetch_chunk_part=$file_remaining_bytes_to_be_downloaded;
}
	$opt[CURLOPT_RANGE]=$sz . "-".($sz+$fetch_chunk_part);
	
}else{
	echo 'New Download.'.PHP_EOL;
	$fetch_chunk_part=$max_file_chunk;
	$opt[CURLOPT_RANGE]="0-".$fetch_chunk_part;

}
$opt[CURLOPT_SSL_VERIFYPEER]=false;
$opt[CURLOPT_FILE]=fopen($to_file, 'a');
$opt[CURLOPT_USERAGENT]='Mozilla/5.0 (Windows NT 6.0; WOW64) AppleWebKit/531.40 (KHTML, like Gecko) Chrome/42.0.1512.133 Safari/531.40';

/*
$opt[CURLOPT_PROXYTYPE]=7;
$opt[CURLOPT_PROXY]="192.168.50.10:8080";
$opt[CURLOPT_HTTPPROXYTUNNEL]=true;
*/

$opt[CURLOPT_SSL_VERIFYPEER]=false;
$opt[CURLOPT_FRESH_CONNECT]=1;
$opt[CURLOPT_FOLLOWLOCATION]=1;

$opt[CURLOPT_SSL_VERIFYHOST]=0;
$opt[CURLOPT_SSL_VERIFYPEER]=0;

curl_setopt_array($ch, $opt);
curl_exec($ch);
$grab_info	= curl_getinfo($ch);
fclose($opt[CURLOPT_FILE]);

echo "\rDownloading Part: ".number_format($ctr)."/".$chunk_count_int;

$ctr++;

}

exit;
