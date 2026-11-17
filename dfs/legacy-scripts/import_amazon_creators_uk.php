<?php
/*
 * import_amazon_creators_uk.php — Amazon UK (feed 41) price fetcher via Creators API.
 * Clone of the US fetcher, pointed at amazon.co.uk. Produces datafiles_amazon/59.csv for Stage B.
 *
 * Usage:
 *   php import_amazon_creators_uk.php test [N]   -> first N entries -> /tmp/59_test.csv (prints sample), NO prod write
 *   php import_amazon_creators_uk.php all        -> full run -> writes 59.csv atomically
 *
 * Row format (matches legacy 59.csv): name, ASIN, image_url(Large), deep_link, display_price
 */

date_default_timezone_set('UTC');
error_reporting(E_ERROR | E_WARNING | E_PARSE);

$MODE  = isset($argv[1]) ? $argv[1] : 'all';
$LIMIT = ($MODE === 'test') ? (isset($argv[2]) ? (int)$argv[2] : 8) : 0;

// ---- config (UK / feed 41) ----
$CID         = '[REDACTED: see _private/CREDENTIALS.md]';
$SEC         = '[REDACTED: see _private/CREDENTIALS.md]';
$TOKEN_URL   = 'https://api.amazon.com/auth/o2/token';
$API         = 'https://creatorsapi.amazon/catalog/v1/searchItems';
$MARKETPLACE = 'www.amazon.co.uk';
$TAG         = 'runshogur-21';
$AMZ_DOMAIN  = 'www.amazon.co.uk';
$EXPORT_URL  = 'https://www.runningshoesguru.com/wp-content/plugins/dstudio/export.cvs';
$OUT_FINAL   = '/var/www/html/admin/datafiles_amazon/59.csv';
$LOG         = '/var/www/html/admin/logs/import_amazon_creators_uk.txt';
$ITEM_COUNT  = 10;
$SLEEP_US    = 300000; // 0.3s between API calls (throttle)

$OUT = ($MODE === 'test')
     ? '/tmp/59_test.csv'
     : '/var/www/html/admin/datafiles_amazon/temp/59-' . time() . '.dsf';

function logmsg($m){ global $LOG; $line = gmdate('Y-m-d H:i:s')." : ".$m."\n"; @file_put_contents($LOG,$line,FILE_APPEND|LOCK_EX); echo $line; }

function getToken(){
  global $CID,$SEC,$TOKEN_URL;
  $ch=curl_init($TOKEN_URL);
  curl_setopt_array($ch,[
    CURLOPT_RETURNTRANSFER=>true, CURLOPT_POST=>true,
    CURLOPT_POSTFIELDS=>http_build_query(['grant_type'=>'client_credentials','client_id'=>$CID,'client_secret'=>$SEC,'scope'=>'creatorsapi::default']),
    CURLOPT_HTTPHEADER=>['Content-Type: application/x-www-form-urlencoded'], CURLOPT_TIMEOUT=>30]);
  $r=curl_exec($ch); $code=curl_getinfo($ch,CURLINFO_HTTP_CODE); curl_close($ch);
  $j=json_decode($r,true);
  if($code==200 && isset($j['access_token'])) return $j['access_token'];
  logmsg("TOKEN ERROR http=$code body=".substr((string)$r,0,200));
  return null;
}

function apiSearch($keywords,$token){
  global $API,$MARKETPLACE,$TAG,$ITEM_COUNT;
  $payload=json_encode([
    'keywords'=>$keywords, 'itemCount'=>$ITEM_COUNT, 'partnerTag'=>$TAG, 'partnerType'=>'Associates',
    'resources'=>['itemInfo.title','images.primary.large','offersV2.listings.price']]);
  $ch=curl_init($API);
  curl_setopt_array($ch,[
    CURLOPT_RETURNTRANSFER=>true, CURLOPT_POST=>true, CURLOPT_POSTFIELDS=>$payload,
    CURLOPT_HTTPHEADER=>["Authorization: Bearer $token","Content-Type: application/json","x-marketplace: $MARKETPLACE"],
    CURLOPT_TIMEOUT=>30]);
  $r=curl_exec($ch); $code=curl_getinfo($ch,CURLINFO_HTTP_CODE); curl_close($ch);
  return [$code,$r];
}

// ---- start ----
logmsg(">>> Creators API UK fetch START mode=$MODE limit=$LIMIT");
$token=getToken();
if(!$token){ logmsg("FATAL: no token"); exit(1); }
$token_time=time();

$exp=@file_get_contents($EXPORT_URL.'?nc='.time());
if($exp===false || strlen($exp)<100){ logmsg("FATAL: export.cvs fetch failed"); exit(1); }
$entries=array_values(array_filter(array_map('trim',explode(',',$exp)), function($x){return strlen($x)>=3;}));
logmsg("export.cvs entries: ".count($entries));

$fp=fopen($OUT,'w');
$kw_count=0; $rows=0; $errors=0; $noprice=0;
foreach($entries as $i=>$entry){
  if($LIMIT && $i>=$LIMIT) break;

  $gender = (stripos($entry,'women_string')!==false) ? 'womens'
          : ((stripos($entry,'men_string')!==false) ? 'mens' : '');
  $name = trim(preg_replace('/<<[^>]*>>/','',$entry));
  if(strlen($name)<3){ continue; }
  $kw = strtolower($name) . ($gender ? (' '.$gender.' running shoes') : '');

  if(time()-$token_time > 3000){ $t=getToken(); if($t){$token=$t;$token_time=time();} }

  list($code,$body)=apiSearch($kw,$token);
  if($code==401){ $t=getToken(); if($t){$token=$t;$token_time=time(); list($code,$body)=apiSearch($kw,$token);} }
  if($code==429){ sleep(2); list($code,$body)=apiSearch($kw,$token); }
  if($code!=200){ $errors++; logmsg("WARN http=$code kw=[$kw] body=".substr((string)$body,0,140)); usleep($SLEEP_US); continue; }

  $j=json_decode($body);
  $items = isset($j->searchResult->items) ? $j->searchResult->items : [];
  $wrote_for_kw=0;
  foreach($items as $it){
    $price = isset($it->offersV2->listings[0]->price->money->amount) ? $it->offersV2->listings[0]->price->money->amount : null;
    if($price===null || $price < 1){ continue; }
    $title = isset($it->itemInfo->title->displayValue) ? $it->itemInfo->title->displayValue : '';
    $asin  = isset($it->asin) ? $it->asin : '';
    if($asin==='' || $title===''){ continue; }
    $img   = isset($it->images->primary->large->url) ? $it->images->primary->large->url : '';
    $deep  = "https://".$AMZ_DOMAIN."/dp/".$asin."?tag=".$TAG."&linkCode=ogi&th=1";
    fputcsv($fp, [$title,$asin,$img,$deep,$price]);
    $rows++; $wrote_for_kw++;
  }
  if($wrote_for_kw===0){ $noprice++; }
  $kw_count++;
  if($kw_count%100==0){ logmsg("processed $kw_count keywords | $rows rows | $errors errors | $noprice no-result"); }
  usleep($SLEEP_US);
}
fclose($fp);

$lines = file($OUT, FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES);
if($lines===false){ $lines=[]; }
$uniq = array_values(array_unique($lines));
logmsg("SUMMARY keywords=$kw_count rows=$rows unique=".count($uniq)." errors=$errors no-result=$noprice");

if($MODE==='test'){
  @file_put_contents($OUT, implode("\n",$uniq)."\n");
  logmsg("TEST output -> $OUT (".count($uniq)." rows). Sample:");
  foreach(array_slice($uniq,0,12) as $l){ echo "  ".$l."\n"; }
} else {
  if(count($uniq) < 50){ logmsg("ABORT: only ".count($uniq)." rows — refusing to overwrite 59.csv (safety guard). Temp left at $OUT"); exit(1); }
  $tmp=$OUT_FINAL.'.new';
  @file_put_contents($tmp, implode("\n",$uniq)."\n");
  @rename($tmp,$OUT_FINAL);
  @chmod($OUT_FINAL,0644);
  @unlink($OUT);
  logmsg("WROTE $OUT_FINAL with ".count($uniq)." rows");
}
logmsg("<<< Creators API UK fetch DONE");
