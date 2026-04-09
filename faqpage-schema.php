<?php
/**
 * Plugin Name: Annie Wright FAQPage Schema
 * Description: Outputs FAQPage JSON-LD schema in wp_head for blog posts that contain FAQ sections (aw-faq-box markup).
 * Version: 1.0.0
 * Author: Annie Wright, LMFT
 * Author URI: https://anniewright.com
 */

if (!defined('ABSPATH')) exit;

/**
 * DISABLED 2026-04-09: The aw-blog-shortcodes plugin (v15+) now outputs
 * FAQPage JSON-LD inline with the [aw_faq] shortcode. This plugin was
 * creating duplicate FAQPage schema on every blog post (227 GSC errors).
 *
 * If you reactivate this, you will get "Duplicate field FAQPAGE" errors
 * in Google Search Console.
 */
function aw_faqpage_schema_output() {
    return; // Duplicate of aw-blog-shortcodes FAQ schema output
    
    if (!is_singular('post')) return;
    
    global $post;
    $content = $post->post_content;
    
    preg_match_all('/aw-faq-question["\']?\s*>(?:Q:\s*)?(.*?)<\/span>/s', $content, $questions);
    preg_match_all('/aw-faq-answer["\']?\s*>(?:A:\s*)?(.*?)<\/p>/s', $content, $answers);
    
    if (empty($questions[1]) || empty($answers[1])) return;
    
    $faq_items = array();
    $count = min(count($questions[1]), count($answers[1]));
    
    for ($i = 0; $i < $count; $i++) {
        $q = strip_tags(html_entity_decode($questions[1][$i]));
        $a = strip_tags(html_entity_decode($answers[1][$i]));
        $q = preg_replace('/^Q:\s*/', '', trim($q));
        $a = preg_replace('/^A:\s*/', '', trim($a));
        
        if ($q && $a) {
            $faq_items[] = array(
                '@type' => 'Question',
                'name' => $q,
                'acceptedAnswer' => array(
                    '@type' => 'Answer',
                    'text' => $a
                )
            );
        }
    }
    
    if (empty($faq_items)) return;
    
    $schema = array(
        '@context' => 'https://schema.org',
        '@type' => 'FAQPage',
        'mainEntity' => $faq_items
    );
    
    echo '<script type="application/ld+json">' . "\n";
    echo json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    echo "\n</script>\n";
}
add_action('wp_head', 'aw_faqpage_schema_output', 20);
