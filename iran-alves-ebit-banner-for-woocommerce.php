<?php

/*
 * Plugin Name: Iran Alves - Ebit Banner para Woocommerce
 * Description: Mostrar o banner do Ebit com dados da venda realizada para avaliação
 * Version: 0.3
 * Author: Iran Alves
 * Author URI: makingpie.com.br
 * License: GPLv3
 * Copyright (C) 2020 Iran
*/

defined('ABSPATH') or die( 'No script kiddies please!' );
define('WC_QSTI_PLUGIN_NAME', 'Iran Alves - Ebit Banner para Woocommerce');
define('WC_QSTI_MAIN_FILENAME', 'iran-alves-ebit-banner-for-woocommerce/iran-alves-ebit-banner-for-woocommerce.php'); ///plugin_basename(__FILE__));

/**
 * Verifica se classe foi iniciada
 * @since 0.1
 */
if( !isset($wc_qsti) || !class_exists('WoocommerceDisplayEbitBanner')) {
    require_once('inc/class-wc-qsti-banner.php');
    require_once('inc/class-wc-qsti-admin.php');    
    $wc_qsti = new WoocommerceDisplayEbitBanner();
}

/**
 * Inicializar plugin
 * @since 0.1
 */
$wc_qsti->init();