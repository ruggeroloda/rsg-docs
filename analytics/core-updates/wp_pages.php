<?php
// Read-only export of page facts for the core-update research (20 Nov 2026). Writes CSV to STDOUT.
global $wpdb;
$types = ['post','page','review','reviews_road','reviews_trail','reviews_training','reviews_gps','gps','comparison','guide','top','content','noindex'];
$in = "'" . implode("','", $types) . "'";
$rows = $wpdb->get_results("SELECT ID, post_type, post_status, post_name, post_date, post_modified, post_author, post_title, post_content FROM {$wpdb->posts} WHERE post_type IN ($in) AND post_status NOT IN ('auto-draft','inherit')");
$rev = [];
foreach ($wpdb->get_results("SELECT post_parent, COUNT(*) n, MIN(post_date) first_rev, MAX(post_date) last_rev, GROUP_CONCAT(DATE_FORMAT(post_date,'%Y-%m') ORDER BY post_date SEPARATOR ' ') months FROM {$wpdb->posts} WHERE post_type='revision' GROUP BY post_parent") as $r) $rev[$r->post_parent] = $r;
$out = fopen('php://stdout', 'w');
fputcsv($out, ['id','post_type','status','url','title','published','modified','text_changed','author','words_content','words_fields','images','links_internal','links_affiliate','revisions','first_revision','last_revision','revision_months','noindex','primary_category']);
foreach ($rows as $p) {
  $meta = $wpdb->get_results($wpdb->prepare("SELECT meta_key, meta_value FROM {$wpdb->postmeta} WHERE post_id=%d", $p->ID));
  $fields = ''; $m = [];
  foreach ($meta as $x) { $m[$x->meta_key] = $x->meta_value;
    if ($x->meta_key[0] !== '_' && strlen($x->meta_value) > 80 && !is_serialized($x->meta_value) && strpos($x->meta_value, 'field_') !== 0) $fields .= ' ' . $x->meta_value; }
  $all = $p->post_content . ' ' . $fields;
  $wc = fn($s) => str_word_count(wp_strip_all_tags($s));
  preg_match_all('/<img\b/i', $all, $im);
  preg_match_all('/href=["\']([^"\']+)/i', $all, $hr);
  $int = 0; $aff = 0;
  foreach ($hr[1] as $h) { if (preg_match('#(amazon\.|amzn\.to|awin1|anrdoezrs|jdoqocy|tkqlhce|dpbolvw|kqzyfj|click\.linksynergy|sjv\.io|avantlink|pjtra|/go/|shareasale|impact|prf\.hn|rakuten)#i', $h)) $aff++; elseif (preg_match('#^(/|https?://(www\.)?runningshoesguru\.com)#i', $h)) $int++; }
  $cats = get_the_terms($p->ID, 'category'); $cat = ($cats && !is_wp_error($cats)) ? $cats[0]->slug : '';
  $r = $rev[$p->ID] ?? null;
  $url = $p->post_status === 'publish' ? wp_make_link_relative(get_permalink($p->ID)) : '';
  $noidx = (isset($m['_yoast_wpseo_meta-robots-noindex']) && $m['_yoast_wpseo_meta-robots-noindex'] == '1') || $p->post_type === 'noindex' ? 1 : 0;
  fputcsv($out, [$p->ID, $p->post_type, $p->post_status, $url, $p->post_title, substr($p->post_date,0,10), substr($p->post_modified,0,10), $m['rsg_content_modified'] ?? '', get_the_author_meta('display_name', $p->post_author), $wc($p->post_content), $wc($fields), count($im[0]), $int, $aff, $r ? $r->n : 0, $r ? substr($r->first_rev,0,10) : '', $r ? substr($r->last_rev,0,10) : '', $r ? $r->months : '', $noidx, $cat]);
}
