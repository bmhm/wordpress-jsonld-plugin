<?php 
/*
Plugin Name:    JSON-LD for Article
Description:    JSON-LD for Article is simply the easiest solution to add valid
                schema.org microdata as a JSON-LD script to your blog posts or articles.
Version:        0.1
Author:         Mikko Piippo, Tomi Lattu
Plugin URI:     http://pluginland.com


JSON-LD is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

JSON-LD for Aricle is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License along
with JSON-LD for Aricle; if not, write to the Free Software Foundation, Inc.,
51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
*/

/**
 * @author Mikko Piippo, Tomi Lattu
 * @since 0.1
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 */


/* Konstanten */
define('JSONLD_DIR', dirname(__FILE__));

/**
 * createArticle
 *
 * @param bool|FALSE $isParent
 */
function createArticle($isParent = false) {
    $article = new Article($isParent);

    // Basic info
    $article->headline = get_the_title();
    $article->datePublished = get_the_date('Y-n-j');
    $article->url = get_permalink();
    $article->setId(get_permalink());

    // Addition info
    $article->articleSection = get_the_category()[0]->cat_name;
    $article->dateModified = get_the_modified_date('Y-n-j');
    $article->commentCount = get_comments_number();

    // Thumbnail if exists

    return $article;
}

/**
 * createAuthorMarkup - create Author Markup
 *
 * @param bool|FALSE $isParent
 */
function createAuthor($isParent = false) {
    $auId = get_the_author_meta( 'ID' );
    $author = new Author($isParent);
    $author->name = get_the_author_meta('display_name');
    $author->url = get_author_posts_url($auId);
    $author->setId(get_author_posts_url($auId));
    $author->email = get_the_author_meta('user_email');

    return $author;
}

function createOrganization($isParent = false) {
    $org = new Organization($isParent);
    $org->name = get_bloginfo('name');
    $org->legalName = get_bloginfo('name');
    $org->setId(network_site_url('/'));
    $org->url = network_site_url('/');
    $org->logo = createLogo();

    return $org;
}

function createImage($isParent = false) {
    $thId = get_post_thumbnail_id();
    $img = new ImageObject($isParent);

    if (has_post_thumbnail()) {
        $img->contentUrl = wp_get_attachment_url($thId);
        $img->image = wp_get_attachment_url($thId);
        $img->setId(get_attachment_link($thId));
        $img->url = wp_get_attachment_url($thId);

        $props = wp_get_attachment_metadata($thId);
        $img->width = $props['width'];
        $img->height = $props['height'];
        $img->caption = wp_prepare_attachment_for_js($thId)['caption'];
    }

    return $img;
}

function createLogo($isParent = false) {
    $logourl = "https://logo.clearbit.com/" . stripProtocolScheme(get_site_url());
    $logo = new ImageObject($isParent);
    $logo->setId($logourl);
    $logo->url = $logourl;
    
    return $logo;
}

function stripProtocolScheme($url) {
   $disallowed = array('http://', 'https://', 'spdy://', '://', '//');

   foreach($disallowed as $d) {
      if(strpos($url, $d) === 0) {
         return str_replace($d, '', $url);
      }
   }

   return $url;
}

function createMainEntity($type = Article, $id = null) {
    return array(
        "@type" => $type,
        "@id" => $id);
}

/**
 * Echoes Markup to your footer.
 * @author Mikko Piippo, Tomi Lattu
 * @since 0.1
 */
function add_markup() {
    $markup = null;

    // Get the data needed for building the JSON-LD
    if (is_single()) {
        $markup = createArticle(true);
        $markup->author = createAuthor();
        $markup->publisher = createOrganization();
        $markup->image = createImage();
        $markup->mainEntityOfPage = createMainEntity('Article', $markup->url);
    } //end if single

    echo '<script type="application/ld+json">'
        . json_encode($markup, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT)
        . '</script>';
} // end function

add_action ('wp_footer','add_markup');


/* Autoload Init */
spl_autoload_register('jsonld_autoload');
            
/* Autoload Funktion */
function jsonld_autoload($class) {
    if ( in_array($class, array('Author', 'Article', 'ImageObject', 'JsonLD', 'Organization')) ) {                                                              
        require_once(
            sprintf('%s/inc/%s.class.php',
                JSONLD_DIR,
                $class));  
    }   
} 
