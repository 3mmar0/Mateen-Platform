<?php
namespace App\Services;
use Carbon\CarbonInterface;
class HijriDateService {
 public function format(?CarbonInterface $date):?string {
  if(!$date)return null; $jd=(int)floor($date->timestamp/86400+2440587.5);
  $l=$jd-1948440+10632; $n=(int)floor(($l-1)/10631); $l=$l-10631*$n+354;
  $j=(int)(floor((10985-$l)/5316)*floor(50*$l/17719)+floor($l/5670)*floor(43*$l/15238));
  $l=$l-(int)floor((30-$j)/15)*(int)floor(17719*$j/50)-(int)floor($j/16)*(int)floor(15238*$j/43)+29;
  $m=(int)floor(24*$l/709); $d=$l-(int)floor(709*$m/24); $y=30*$n+$j-30;
  return sprintf('%02d/%02d/%04d هـ',$d,$m,$y);
 }
}
