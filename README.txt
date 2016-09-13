=== Plugin Name ===
Contributors: writegnj
Donate link: http://www.ohsikpark.com/
Tags: follow author, favorite author, follow, favorite, bookmark author, bookmark, subscribe author, subscribe, like author, like
Requires at least: 3.0.1
Tested up to: 4.6.1
Stable tag: 4.6.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

== Description ==
Favorite Authors allows you to add all of your favorite authors on your account.
Simply add `<?php fav_authors_link(); ?>` on your template files to display Favorite button and use `[favorite-authors-list]` shortcode on page or post to show favorited author list.

Please email me for any comments or questions o@ohsikpark.com

*Github*
https://github.com/ohsik/favorite-authors


== Installation ==

1. Upload `favorite-authors` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Place `<?php fav_authors_link(); ?>` on your template files where you want to display Favorite button
3. Place `[favorite-authors-list]` in your page/post to display list of favorited author list


== Frequently Asked Questions ==

= How to use? =
Add this PHP snippet where you want to display `Favorite author` button `<?php fav_authors_link(); ?>`

= How to show faorited authors on a page? =
Use `[favorite-authors-list]` on your page or post to display list of favorited authors.

= How to change number of authors displayed =
By default, it shows 20 authors per page. Change `$limit = 20;` on `fav_authors_pagi()` and `fav_authors_get_list()` in `favorite-authors.php` file.


== Screenshots ==

1. Favorite button
2. Favorited author
3. Show all favorited authors


== Changelog ==

= 1.0 =
* Initial release

= 1.1 =
* Favorited author number added

= 1.2 =
* Replaced get_currentuserinfo() with wp_get_current_user() for WordPress 4.5
