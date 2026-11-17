<?php
/**
 * RSG review "specs & tester assessment" block (road).
 * Renders objective facts + the tester's size/fit (shoe diagram), cushioning &
 * ride (named-pole spectrums) and usage (chips) from ACF, inheriting the site's
 * native typography. Included by single-reviews_road.php in place of the old
 * dark specs panel. Shipped 2026-06-23.
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

$pid  = get_the_ID();
$pt   = get_post_type( $pid );
$map  = function_exists( 'rsg_review_assessment_map' ) ? rsg_review_assessment_map( $pt ) : array();
$vote = get_field( 'vote', $pid );

if ( ! function_exists( 'rsgp_choice_label' ) ) {
	function rsgp_choice_label( $o, $val ) {
		if ( ! empty( $o['choices'] ) && isset( $o['choices'][ $val ] ) ) { return (string) $o['choices'][ $val ]; }
		return is_string( $val ) ? $val : '';
	}
}
if ( ! function_exists( 'rsgp_idx' ) ) {
	function rsgp_idx( $o, $val ) {
		$keys = ! empty( $o['choices'] ) ? array_keys( $o['choices'] ) : array();
		$pos  = 0;
		foreach ( $keys as $k ) { if ( (string) $k === (string) $val ) { return array( $pos, count( $keys ) ); } $pos++; }
		return array( -1, count( $keys ) );
	}
}
if ( ! function_exists( 'rsgp_marker_pct' ) ) {
	function rsgp_marker_pct( $idx, $count ) {
		if ( $count <= 1 || $idx < 0 ) { return 50; }
		return round( 6 + ( $idx / ( $count - 1 ) ) * 88 );
	}
}
if ( ! function_exists( 'rsgp_fit' ) ) {
	function rsgp_fit( $pid, $field ) {
		$o = get_field_object( $field, $pid );
		if ( ! $o ) { return null; }
		$val = isset( $o['value'] ) ? $o['value'] : '';
		if ( '' === $val || null === $val ) { return null; }
		return array( 'label' => ucfirst( rsgp_choice_label( $o, $val ) ), 'dev' => ( 'normal' !== strtolower( trim( (string) $val ) ) ) );
	}
}
if ( ! function_exists( 'rsgp_render_ride_row' ) ) {
	function rsgp_render_ride_row( $pid, $field, $meta ) {
		$o = get_field_object( $field, $pid );
		if ( ! $o ) { return; }
		$val = isset( $o['value'] ) ? $o['value'] : '';
		if ( '' === $val || null === $val ) { return; }
		list( $idx, $count ) = rsgp_idx( $o, $val );
		$pct   = rsgp_marker_pct( $idx, $count );
		$label = ucfirst( rsgp_choice_label( $o, $val ) );
		echo '<div style="margin-bottom:1.1rem;">';
		echo '<div style="margin-bottom:8px;"><span class="text-muted" style="font-size:1rem;">' . esc_html( $meta[0] ) . '</span><strong style="font-size:1.05rem;font-weight:bold;color:#151515;margin-left:8px;">' . esc_html( $label ) . '</strong></div>';
		echo '<div style="display:flex;align-items:center;gap:12px;max-width:500px;margin-left:auto;">';
		echo '<span class="text-muted" style="font-size:0.8rem;flex:none;">' . esc_html( $meta[1] ) . '</span>';
		echo '<div style="position:relative;flex:1;height:12px;"><div style="position:absolute;top:4px;left:0;right:0;height:4px;background:#E6E6E9;border-radius:3px;"></div><div style="position:absolute;top:0;left:' . $pct . '%;width:12px;height:12px;border-radius:50%;background:#2189E1;transform:translateX(-50%);box-shadow:0 0 0 3px #fff,0 0 0 4px #BFD9F2;"></div></div>';
		echo '<span class="text-muted" style="font-size:0.8rem;flex:none;">' . esc_html( $meta[2] ) . '</span>';
		echo '</div>';
		echo '</div>';
	}
}
if ( ! function_exists( 'rsgp_render_chips' ) ) {
	function rsgp_render_chips( $pid, $field, $heading ) {
		$o = get_field_object( $field, $pid );
		if ( ! $o || empty( $o['choices'] ) ) { return; }
		$sel = function_exists( 'rsg_assessment_usage_values' ) ? rsg_assessment_usage_values( $pid, $field ) : array();
		$sel = array_map( 'strtolower', array_map( 'trim', $sel ) );
		echo '<div style="margin-top:1.4rem;padding-top:1.2rem;border-top:1px solid #E6E6E9;display:flex;align-items:center;gap:10px;flex-wrap:wrap;">';
		echo '<span class="text-muted" style="font-size:1rem;">' . esc_html( $heading ) . '</span>';
		foreach ( $o['choices'] as $label ) {
			$is = in_array( strtolower( trim( $label ) ), $sel, true );
			if ( $is ) {
				echo '<span style="background:#EAF3FC;color:#185FA5;font-size:0.9rem;font-weight:600;padding:6px 14px;border-radius:20px;display:inline-flex;align-items:center;gap:6px;"><i class="ti ti-check" style="font-size:1rem;" aria-hidden="true"></i>' . esc_html( ucfirst( $label ) ) . '</span>';
			} else {
				echo '<span style="color:#9CA3AA;font-size:0.9rem;padding:6px 14px;border-radius:20px;border:1px solid #E3E6EA;">' . esc_html( ucfirst( $label ) ) . '</span>';
			}
		}
		echo '</div>';
	}
}

/* facts (heel/toe handled by the stack graphic, not tiles) */
$facts = array();
$w = get_field( 'weight_oz', $pid );    if ( $w !== '' && $w !== null && floatval( $w ) > 0 ) { $facts[] = array( 'Weight', number_format( floatval( $w ), 1 ) . ' oz' ); }
$d = get_field( 'heel_drop_mm', $pid );  if ( $d !== '' && $d !== null )                       { $facts[] = array( 'Drop', intval( $d ) . ' mm' ); }
if ( 'reviews_road' === $pt ) { $plate = get_field( 'carbon_plate', $pid ); if ( $plate ) { $facts[] = array( 'Carbon plate', ucfirst( $plate ) ); } }
if ( function_exists( 'rsg_schema_offer' ) ) { $off = rsg_schema_offer( $pid ); if ( ! empty( $off['price'] ) ) { $facts[] = array( 'Price', '$' . intval( $off['price'] ) ); } }
$types = get_the_terms( $pid, 'shoes_type' );
if ( $types && ! is_wp_error( $types ) ) { $facts[] = array( 'Category', $types[0]->name ); }

$heel_mm = intval( get_field( 'heel_mm', $pid ) );
$toe_mm  = intval( get_field( 'toe_mm', $pid ) );

/* fit */
$heel = rsgp_fit( $pid, 'heel_fit' );
$mid  = rsgp_fit( $pid, 'midfoot_fit' );
$toe  = rsgp_fit( $pid, 'toebox_fit' );

$so = get_field_object( 'sizing', $pid );
$sizing_html = '';
if ( $so && '' !== $so['value'] && null !== $so['value'] ) {
	$slabel = rsgp_choice_label( $so, $so['value'] );
	$snum   = intval( $so['value'] );
	if ( $snum > 0 )     { $lead = 'Runs small'; $icon = 'ti-arrow-up-right';   $dev = true; }
	elseif ( $snum < 0 ) { $lead = 'Runs large'; $icon = 'ti-arrow-down-right'; $dev = true; }
	else                 { $lead = 'True to size'; $icon = 'ti-check';          $dev = false; }
	$bg = $dev ? '#EAF3FC' : '#F5F7F9'; $fg = $dev ? '#185FA5' : '#5F6A72';
	$txt = $dev ? ( '<strong style="font-weight:600;">' . esc_html( $lead ) . '</strong> — ' . esc_html( lcfirst( $slabel ) ) ) : '<strong style="font-weight:600;">' . esc_html( $lead ) . '</strong>';
	$sizing_html = '<div style="display:flex;align-items:center;gap:10px;background:' . $bg . ';border-radius:10px;padding:11px 15px;margin:12px 0 18px;"><span style="color:' . $fg . ';display:inline-flex;"><i class="ti ' . $icon . '" style="font-size:1.2rem;" aria-hidden="true"></i></span><span style="font-size:0.95rem;color:' . $fg . ';">' . $txt . '</span></div>';
}

$ride_meta = array(
	'cushioning_type'   => array( 'Type of cushioning', 'Responsive', 'Plush' ),
	'cushioning_amount' => array( 'Amount of cushioning', 'Little', 'High' ),
	'stability'         => array( 'Stability', 'Neutral', 'Stable' ),
	'flexibility'       => array( 'Flexibility', 'Rigid', 'Flexible' ),
	'lugs'              => array( 'Grip (lugs)', 'Low', 'High' ),
);

/* stack side-profile geometry */
$gw = 300; $base = 78; $maxbar = 48;
$mx = max( $heel_mm, $toe_mm, 1 );
$heel_top = $base - ( $heel_mm / $mx ) * $maxbar;
$toe_top  = $base - ( $toe_mm / $mx ) * $maxbar;

/* internal nav links (preserve the SEO internal-linking the old panel carried) */
$navlinks   = array();
$brand_term = get_field( 'brand', $pid );
if ( is_object( $brand_term ) && isset( $brand_term->slug ) ) {
	$navlinks[] = '<a href="/reviews/' . esc_attr( $brand_term->slug ) . '/all/">' . esc_html( $brand_term->name ) . '</a>';
}
if ( get_field( 'family', $pid ) ) {
	$fam = get_the_terms( $pid, 'shoes_family' );
	if ( $fam && ! is_wp_error( $fam ) ) {
		$navlinks[] = '<a href="' . esc_url( get_term_link( $fam[0] ) ) . '" rel="tag">' . esc_html( ucfirst( $fam[0]->name ) ) . ' family</a>';
	}
}
$prev = get_field( 'previous_model', $pid );
if ( $prev && is_object( $prev ) ) {
	$prev_name = trim( (string) get_field( 'model', $prev->ID ) );
	if ( '' === $prev_name ) { $prev_name = get_the_title( $prev->ID ); }
	$navlinks[] = '<span style="color:#8A9299;">Previous:</span> <a href="' . esc_url( get_permalink( $prev->ID ) ) . '">' . esc_html( $prev_name ) . '</a>';
}
?>
<div class="spacer-l"></div>
<div style="border-top:1px solid #E6E6E9;padding-top:1.5rem;color:#212529;">

	<?php if ( ! empty( $navlinks ) ) : ?>
		<div style="font-size:0.9rem;color:#6B7280;margin-bottom:1rem;"><?php echo implode( ' &nbsp;&middot;&nbsp; ', $navlinks ); ?></div>
	<?php endif; ?>

	<?php if ( ! empty( $facts ) ) : ?>
		<h3 style="margin:0 0 1rem;">The facts</h3>
		<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(104px,1fr));gap:10px;">
			<?php foreach ( $facts as $f ) : ?>
				<div style="background:#F5F6F7;border-radius:4px;padding:11px 13px;"><div class="text-muted" style="font-size:0.85rem;"><?php echo esc_html( $f[0] ); ?></div><div style="font-size:1.1rem;font-weight:bold;color:#151515;"><?php echo esc_html( $f[1] ); ?></div></div>
			<?php endforeach; ?>
		</div>
		<?php if ( $heel_mm && $toe_mm ) : ?>
			<div style="background:#F5F6F7;border-radius:4px;padding:14px 16px;margin-top:10px;display:flex;align-items:center;gap:18px;flex-wrap:wrap;">
				<svg viewBox="0 0 300 96" style="width:240px;height:auto;flex:none;" aria-hidden="true">
					<line x1="14" y1="<?php echo $base; ?>" x2="286" y2="<?php echo $base; ?>" stroke="#D4D9DE" stroke-width="1.5"/>
					<polygon points="40,<?php echo $base; ?> 40,<?php echo round( $heel_top ); ?> 260,<?php echo round( $toe_top ); ?> 260,<?php echo $base; ?>" fill="#DCEBFA" stroke="#7FB4E8" stroke-width="1.5"/>
					<line x1="40" y1="<?php echo round( $heel_top ) - 4; ?>" x2="40" y2="20" stroke="#C9D0D6" stroke-width="1" stroke-dasharray="3 3"/>
					<line x1="260" y1="<?php echo round( $toe_top ) - 4; ?>" x2="260" y2="20" stroke="#C9D0D6" stroke-width="1" stroke-dasharray="3 3"/>
					<text x="40" y="15" font-family="var(--font-family-body)" font-size="13" fill="#151515" font-weight="bold" text-anchor="middle"><?php echo $heel_mm; ?> mm</text>
					<text x="260" y="15" font-family="var(--font-family-body)" font-size="13" fill="#151515" font-weight="bold" text-anchor="middle"><?php echo $toe_mm; ?> mm</text>
					<text x="40" y="92" font-family="var(--font-family-body)" font-size="12" fill="#8A9299" text-anchor="middle">Heel</text>
					<text x="260" y="92" font-family="var(--font-family-body)" font-size="12" fill="#8A9299" text-anchor="middle">Toe</text>
				</svg>
				<div style="flex:1;min-width:120px;">
					<div class="text-muted" style="font-size:0.85rem;">Stack height</div>
					<div style="font-size:1.1rem;font-weight:bold;color:#151515;"><?php echo $heel_mm; ?> mm heel · <?php echo $toe_mm; ?> mm toe</div>
					<div class="text-muted" style="font-size:0.9rem;margin-top:2px;"><?php echo ( $heel_mm - $toe_mm ); ?> mm drop</div>
				</div>
			</div>
		<?php endif; ?>
	<?php endif; ?>

	<div style="display:flex;align-items:center;gap:8px;margin:1.8rem 0 0.3rem;">
		<span style="color:#2189E1;display:inline-flex;"><i class="ti ti-user-check" style="font-size:1.1rem;" aria-hidden="true"></i></span>
		<span style="font-size:0.95rem;color:#2189E1;font-weight:bold;">Our tester's take</span>
		<span class="text-muted" style="font-size:0.95rem;">— hands-on judgments, not specs</span>
	</div>

	<?php if ( $heel || $mid || $toe || $sizing_html ) : ?>
		<h3 style="margin:1rem 0 0;">Size &amp; fit</h3>
		<?php echo $sizing_html; ?>
		<div style="display:flex;align-items:center;gap:22px;flex-wrap:wrap;margin-bottom:6px;max-width:560px;">
			<svg viewBox="0 0 300 150" style="width:230px;height:auto;flex:none;" aria-hidden="true">
				<path d="M30,75 C28,60 40,52 56,50 C110,43 150,38 210,32 C245,29 272,42 276,62 C280,80 268,98 240,104 C180,114 110,110 56,100 C40,97 32,90 30,75 Z" fill="#EEF1F4" stroke="#C9D0D6" stroke-width="1.5"/>
				<?php if ( $heel && $heel['dev'] ) : ?><path d="M30,75 C28,60 40,52 56,50 C75,48 90,46 100,45 L100,103 C90,103 75,103 56,100 C40,97 32,90 30,75 Z" fill="#DCEBFA" stroke="#7FB4E8" stroke-width="1.2"/><?php endif; ?>
				<?php if ( $mid && $mid['dev'] ) : ?><path d="M100,45 C140,40 170,37 196,34 L196,108 C170,108 140,108 100,103 Z" fill="#DCEBFA" stroke="#7FB4E8" stroke-width="1.2"/><?php endif; ?>
				<?php if ( $toe && $toe['dev'] ) : ?><path d="M196,34 C245,29 272,42 276,62 C280,80 268,98 240,104 C220,108 210,108 196,108 Z" fill="#DCEBFA" stroke="#7FB4E8" stroke-width="1.2"/><?php endif; ?>
				<line x1="100" y1="46" x2="100" y2="104" stroke="#C9D0D6" stroke-width="1" stroke-dasharray="3 3"/>
				<line x1="196" y1="35" x2="196" y2="108" stroke="#C9D0D6" stroke-width="1" stroke-dasharray="3 3"/>
				<text x="62" y="82" font-family="var(--font-family-body)" font-size="13" fill="<?php echo ( $heel && $heel['dev'] ) ? '#185FA5' : '#8A9299'; ?>" text-anchor="middle">Heel</text>
				<text x="148" y="80" font-family="var(--font-family-body)" font-size="13" fill="<?php echo ( $mid && $mid['dev'] ) ? '#185FA5' : '#8A9299'; ?>" text-anchor="middle">Midfoot</text>
				<text x="236" y="72" font-family="var(--font-family-body)" font-size="13" fill="<?php echo ( $toe && $toe['dev'] ) ? '#185FA5' : '#8A9299'; ?>" text-anchor="middle">Toe</text>
			</svg>
			<div style="flex:1;min-width:160px;">
				<?php
				foreach ( array( array( 'Heel', $heel ), array( 'Midfoot', $mid ), array( 'Toebox', $toe ) ) as $i => $z ) :
					if ( ! $z[1] ) { continue; }
					$brd = ( $i < 2 ) ? 'border-bottom:1px solid #ECEDEF;' : '';
				?>
					<div style="display:flex;justify-content:space-between;padding:10px 0;<?php echo $brd; ?>"><span class="text-muted" style="font-size:1rem;"><?php echo esc_html( $z[0] ); ?></span><?php if ( $z[1]['dev'] ) : ?><strong style="font-size:1rem;font-weight:bold;color:#185FA5;"><?php echo esc_html( $z[1]['label'] ); ?> <i class="ti ti-alert-circle" style="font-size:0.9rem;vertical-align:-1px;" aria-hidden="true"></i></strong><?php else : ?><span class="text-muted" style="font-size:1rem;"><?php echo esc_html( $z[1]['label'] ); ?></span><?php endif; ?></div>
				<?php endforeach; ?>
			</div>
		</div>
	<?php endif; ?>

	<?php if ( ! empty( $map['ride'] ) ) : ?>
		<h3 style="margin:1.7rem 0 1.2rem;padding-top:1.4rem;border-top:1px solid #E6E6E9;">Cushioning &amp; ride</h3>
		<?php foreach ( $map['ride'] as $f ) { if ( isset( $ride_meta[ $f ] ) ) { rsgp_render_ride_row( $pid, $f, $ride_meta[ $f ] ); } } ?>
	<?php endif; ?>

	<?php
	if ( ! empty( $map['usage'] ) ) {
		foreach ( $map['usage'] as $uf ) {
			$heading = ( 'good_for_running' === $uf ) ? 'Good for running' : 'Best for';
			rsgp_render_chips( $pid, $uf, $heading );
		}
	}
	?>

</div>
<?php
