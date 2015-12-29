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


/**
 * createArticle
 *
 * @param bool|FALSE $isParent
 */
function createArticle($isParent = false) {

    $article = new Article(isParent);
    $article->headline = get_the_title();
    $article->datePublished = get_the_date('Y-n-j');
    $article->url = get_permalink();

    return $article;
}

/**
 * createAuthorMarkup - create Author Markup
 *
 * @param bool|FALSE $isParent
 */
function createAuthor($isParent = false) {
    $author = new Author(isParent);
    $author->name = get_the_author();
    $author->url = get_the_author_meta("user_url");

    return $author;
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
        $markup = createArticleMarkup(true);
        $markup->author = createAuthor();

        // TODO: Implement everything below.
        $articlepublisher=get_bloginfo('name');
        $articlesection=get_the_category()[0]->cat_name;
        $articlemodified=get_the_modified_date();
        $articlecommentcount=get_comments_number();

        if (has_post_thumbnail()) {
            $thumbnailurl=wp_get_attachment_url(get_post_thumbnail_id());
        }

        $pub = array (
            '@type' => 'Organization',
            'name'  => $articlepublisher);
    } //end if single

    echo '<script type="application/ld+json">'
        . json_encode($markup, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT)
        . '</script>';
} // end function
add_action ('wp_footer','add_markup');

