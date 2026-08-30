<?php
/*
Template Name: A+ Esthetic Approved Snapshot
Template Post Type: page
*/
if (!defined('ABSPATH')) { exit; }

$post_id = get_queried_object_id();
$snapshot_html = get_post_meta($post_id, '_aesthetic_snapshot_html', true);

if (!$snapshot_html) {
    get_header();
    while (have_posts()) { the_post(); the_content(); }
    get_footer();
    return;
}

$parts = aesthetic_snapshot_extract($snapshot_html);
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo('charset'); ?>">
<meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
<?php wp_head(); ?>
<?php echo $parts['head']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- trusted migration snapshot, admin-only import. ?>
<style id="aesthetic-mobile-shell-v106">
/* Universal mobile navigation for every approved snapshot. */
.aesthetic-mobile-menu-toggle,
.aesthetic-mobile-menu{display:none}
@media (max-width:1000px){
  body.aesthetic-snapshot-active .aesthetic-snapshot-root .nav{display:none!important}
  .aesthetic-mobile-menu-toggle{
    display:flex;position:fixed;z-index:2147481000;top:max(16px,env(safe-area-inset-top));right:16px;
    width:48px;height:48px;border:1px solid rgba(255,255,255,.34);background:rgba(18,13,10,.76);
    backdrop-filter:blur(14px);-webkit-backdrop-filter:blur(14px);align-items:center;justify-content:center;
    padding:0;cursor:pointer;color:#fff;border-radius:0;box-shadow:0 8px 28px rgba(0,0,0,.16)
  }
  body.admin-bar .aesthetic-mobile-menu-toggle{top:calc(46px + env(safe-area-inset-top))}
  .aesthetic-mobile-menu-toggle span,
  .aesthetic-mobile-menu-toggle:before,
  .aesthetic-mobile-menu-toggle:after{content:"";position:absolute;width:21px;height:1px;background:currentColor;transition:transform .28s ease,opacity .2s ease}
  .aesthetic-mobile-menu-toggle span{transform:translateY(0)}
  .aesthetic-mobile-menu-toggle:before{transform:translateY(-7px)}
  .aesthetic-mobile-menu-toggle:after{transform:translateY(7px)}
  .aesthetic-menu-open .aesthetic-mobile-menu-toggle span{opacity:0}
  .aesthetic-menu-open .aesthetic-mobile-menu-toggle:before{transform:rotate(45deg)}
  .aesthetic-menu-open .aesthetic-mobile-menu-toggle:after{transform:rotate(-45deg)}
  .aesthetic-mobile-menu{
    display:flex;position:fixed;z-index:2147480000;inset:0;background:#130f0c;color:#fff;
    padding:max(92px,calc(78px + env(safe-area-inset-top))) 24px max(32px,env(safe-area-inset-bottom));
    opacity:0;visibility:hidden;pointer-events:none;transform:translateY(-8px);
    transition:opacity .25s ease,visibility .25s ease,transform .25s ease;overflow:auto
  }
  body.admin-bar .aesthetic-mobile-menu{padding-top:max(132px,calc(118px + env(safe-area-inset-top)))}
  .aesthetic-menu-open .aesthetic-mobile-menu{opacity:1;visibility:visible;pointer-events:auto;transform:none}
  .aesthetic-menu-open{overflow:hidden!important}
  .aesthetic-mobile-menu-inner{width:min(100%,620px);margin:auto;display:flex;flex-direction:column;min-height:100%}
  .aesthetic-mobile-menu-brand{font-family:"Bodoni 72","Didot","Iowan Old Style","Times New Roman",serif;font-size:26px;padding-bottom:22px;border-bottom:1px solid rgba(255,255,255,.13)}
  .aesthetic-mobile-menu-brand small{display:block;margin-top:5px;font:600 8px/1.2 Inter,system-ui,sans-serif;letter-spacing:.3em;text-transform:uppercase;color:rgba(255,255,255,.55)}
  .aesthetic-mobile-menu-links{display:grid;grid-template-columns:1fr 1fr;margin-top:14px;border-top:1px solid rgba(255,255,255,.1)}
  .aesthetic-mobile-menu-links a{display:flex;align-items:center;min-height:58px;padding:13px 8px;border-bottom:1px solid rgba(255,255,255,.1);font:400 17px/1.15 "Bodoni 72","Didot","Iowan Old Style","Times New Roman",serif;color:#fff!important;text-decoration:none!important}
  .aesthetic-mobile-menu-links a:nth-child(odd){border-right:1px solid rgba(255,255,255,.1);padding-right:16px}
  .aesthetic-mobile-menu-links a:nth-child(even){padding-left:16px}
  .aesthetic-mobile-book{display:flex!important;justify-content:center;align-items:center;margin-top:24px;padding:16px 20px;background:#c0924f!important;color:#fff!important;text-decoration:none!important;font:700 10px/1 Inter,system-ui,sans-serif;letter-spacing:.16em;text-transform:uppercase}
  .aesthetic-mobile-menu-meta{margin-top:auto;padding-top:28px;font:400 10px/1.65 Inter,system-ui,sans-serif;color:rgba(255,255,255,.5)}
}
@media (max-width:650px){
  body.aesthetic-snapshot-active .aesthetic-snapshot-root .header-cta{display:none!important}
  .aesthetic-mobile-menu-toggle{top:max(12px,env(safe-area-inset-top));right:12px;width:44px;height:44px}
  body.admin-bar .aesthetic-mobile-menu-toggle{top:calc(44px + env(safe-area-inset-top))}
  .aesthetic-mobile-menu-links{grid-template-columns:1fr}
  .aesthetic-mobile-menu-links a:nth-child(n){border-right:0;padding-left:4px;padding-right:4px;min-height:52px}

  /* Home: do not stretch the 1672x941 embedded hero raster across a ~980px portrait canvas. */
  body.aesthetic-route-home .aesthetic-snapshot-root .hero{min-height:auto!important;display:flex!important;flex-direction:column!important;background:#17120e!important;overflow:hidden!important}
  body.aesthetic-route-home .aesthetic-snapshot-root .hero-media{
    position:relative!important;inset:auto!important;order:0;width:100%!important;height:clamp(330px,92vw,390px)!important;
    object-fit:cover!important;object-position:68% center!important;image-rendering:auto!important;flex:none!important
  }
  body.aesthetic-route-home .aesthetic-snapshot-root .hero:after{
    top:0!important;bottom:auto!important;height:clamp(330px,92vw,390px)!important;
    background:linear-gradient(180deg,rgba(12,9,7,.08) 0%,rgba(12,9,7,.02) 60%,rgba(12,9,7,.7) 100%)!important;pointer-events:none!important
  }
  body.aesthetic-route-home .aesthetic-snapshot-root .hero-copy{
    order:1!important;min-height:0!important;width:auto!important;margin:0!important;padding:38px 18px 132px!important;justify-content:flex-start!important;background:#17120e!important
  }
  body.aesthetic-route-home .aesthetic-snapshot-root .hero-title{font-size:clamp(39px,11vw,46px)!important}
}
@media (prefers-reduced-motion:reduce){
  .aesthetic-mobile-menu,.aesthetic-mobile-menu-toggle span,.aesthetic-mobile-menu-toggle:before,.aesthetic-mobile-menu-toggle:after{transition:none!important}
}
</style>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<div class="aesthetic-snapshot-root">
<?php echo $parts['body']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- trusted migration snapshot, admin-only import. ?>

<button class="aesthetic-mobile-menu-toggle" type="button" aria-label="Menü öffnen" aria-controls="aesthetic-mobile-menu" aria-expanded="false"><span></span></button>
<nav class="aesthetic-mobile-menu" id="aesthetic-mobile-menu" aria-label="Mobile Hauptnavigation" aria-hidden="true">
  <div class="aesthetic-mobile-menu-inner">
    <a class="aesthetic-mobile-menu-brand" href="/">A+ Esthetic<small>Frankfurt</small></a>
    <div class="aesthetic-mobile-menu-links">
      <a href="/behandlungen/">Behandlungen</a>
      <a href="/botox-behandlungen/">Botox</a>
      <a href="/hyaluronsaure-behandlungen/">Hyaluronsäure</a>
      <a href="/skinbooster/">Skinbooster</a>
      <a href="/prp-behandlung/">PRP</a>
      <a href="/infusionstherapien/">Infusionen</a>
      <a href="/injektions-lipolyse/">Injektions-Lipolyse</a>
      <a href="/rf-microneedling/">RF-Microneedling</a>
      <a href="/laser-behandlungen/">Laser</a>
      <a href="/kontakt/">Kontakt</a>
    </div>
    <a class="aesthetic-mobile-book" href="https://www.doctolib.de/privatpraxis/frankfurt-am-main/a-esthetic-zentrum-fuer-aesthetik" target="_blank" rel="noopener">Termin buchen · Doctolib</a>
    <div class="aesthetic-mobile-menu-meta">Stiftstrasse 14 · 60313 Frankfurt am Main · 2. OG</div>
  </div>
</nav>
<script id="aesthetic-mobile-menu-js">
(function(){
  var toggle=document.querySelector('.aesthetic-mobile-menu-toggle');
  var menu=document.getElementById('aesthetic-mobile-menu');
  if(!toggle||!menu)return;
  function setOpen(open){
    document.body.classList.toggle('aesthetic-menu-open',open);
    toggle.setAttribute('aria-expanded',open?'true':'false');
    toggle.setAttribute('aria-label',open?'Menü schließen':'Menü öffnen');
    menu.setAttribute('aria-hidden',open?'false':'true');
  }
  toggle.addEventListener('click',function(){setOpen(!document.body.classList.contains('aesthetic-menu-open'));});
  menu.addEventListener('click',function(e){if(e.target.closest('a'))setOpen(false);});
  document.addEventListener('keydown',function(e){if(e.key==='Escape')setOpen(false);});
  window.addEventListener('resize',function(){if(window.innerWidth>1000)setOpen(false);},{passive:true});
}());
</script>
</div>
<div class="aesthetic-snapshot-warning">A+ migration snapshot</div>
<?php wp_footer(); ?>
</body>
</html>
