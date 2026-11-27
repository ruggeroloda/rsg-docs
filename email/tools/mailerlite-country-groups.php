<?php
// Copy of ~/mlg.php on Rocket.net (27 Nov 2026). Run from a Mac-linked chat on Rocket.net, from ~/public_html:
//   K=$(php -r '<read RSG_MAILERLITE_TOKEN from wp-config.php>') && K="$K" nohup php ~/mlg.php > ~/mlg.log 2>&1 &
// Pages through all active subscribers (~40 pages) and imports into 3 groups: ~8-10 min. Token never printed or stored here.
// BF2026 country groups: Country field decides when filled; when empty, MailerLite location (country_id) decides; both empty = USA.
// Adds active subscribers to the groups (email only; fields untouched; no autoresponders). Re-runnable: existing members are skipped by MailerLite.
$k=getenv("K");
function ml($m,$p,$b=null){ global $k; $c=curl_init("https://api.mailerlite.com/api/v2/$p");
  $o=[CURLOPT_RETURNTRANSFER=>1,CURLOPT_CUSTOMREQUEST=>$m,CURLOPT_HTTPHEADER=>["X-MailerLite-ApiKey: $k","Content-Type: application/json"],CURLOPT_TIMEOUT=>120];
  if($b!==null) $o[CURLOPT_POSTFIELDS]=json_encode($b); curl_setopt_array($c,$o); $r=curl_exec($c); return [curl_getinfo($c,CURLINFO_HTTP_CODE),json_decode($r,true),$r]; }
$US=["United States","Puerto Rico","U.S. Virgin Islands","Virgin Islands, U.S.","Guam","Northern Mariana Islands"];
$want=["BF2026 - USA"=>[],"BF2026 - UK"=>[],"BF2026 - AU"=>[]];
for($o=0;;$o+=1000){ [$h,$d]=ml("GET","subscribers?type=active&limit=1000&offset=$o"); if(!$d) break;
  foreach($d as $s){ $cf=""; foreach($s["fields"] as $f) if($f["key"]=="country") $cf=trim((string)$f["value"]);
    $id=(string)($s["country_id"]??""); if($id==="0") $id="";
    if($cf!==""){ $g=in_array($cf,$US)?"USA":($cf==="United Kingdom"?"UK":($cf==="Australia"?"AU":null)); }
    else { $g=($id===""||$id==="465")?"USA":($id==="21"?"UK":($id==="95"?"AU":null)); }
    if($g) $want["BF2026 - $g"][]=$s["email"]; }
  if(count($d)<1000) break; usleep(200000); }
[$h,$groups]=ml("GET","groups?limit=200"); $ids=[]; foreach($groups as $g) $ids[$g["name"]]=$g["id"];
foreach($want as $name=>$emails){
  if(empty($ids[$name])){ [$h,$g]=ml("POST","groups",["name"=>$name]); $ids[$name]=$g["id"]; echo "created $name ",$g["id"],"\n"; }
  $ok=0; foreach(array_chunk($emails,500) as $ch){
    [$h,$r,$raw]=ml("POST","groups/".$ids[$name]."/subscribers/import",["subscribers"=>array_map(fn($e)=>["email"=>$e],$ch),"resubscribe"=>false,"autoresponders"=>false]);
    if($h!=200){ echo "ERR $name $h ",substr($raw,0,200),"\n"; break; } $ok+=count($ch); usleep(300000); }
  echo "$name: matched ",count($emails),", sent to import $ok\n"; }
