=== Plugin Name ===
Contributors: jshare, SEO ROI Services
Tags: seo, marketing
Requires at least: 3.0
Tested up to: 3.2
Stable tag: 1.0

This SEO plugin consolidates ordinary posts on a topic into one authoritative post, increasing longtail traffic, PageRank per page and linkability.

== Description ==

Content Marketing Cannon is an advanced SEO plugin that does 3 things. 1) It increases content linkability and authority. 2) It consolidates PageRank so each post stands 
a better chance at ranking. 3) It lifts chances to get longtail traffic.

The problem: Blogs can only include so many posts on a category page before you need to paginate. And if you paginate, posts on page 2 etc won't get as much link juice. 
Sure, you can set posts to appear that to 100 posts, but then the category page will take a while to load which isn't ideal either.
 
I offered a few solutions to this problem in my advanced SEO book (http://book.seoroi.com ), one of which was:
 
Consolidate posts on a specific topic into a single authority article.
 
1) You get an in-depth authority article that is more likely to attract links than smaller bits of info.
2) The link juice from those articles consolidates onto 1 page, making it more competitive.
3) There are more possible longtail combinations to hit.
4) You reduce the number of posts and thus have more PageRank flowing from the categories into posts . Also, you reduce the need for pagination.
 
Problem is this demands a fair bit of manual work...
 
Unless you automated it.
 
Oh wait! That's what the plugin - Content Marketing Cannon - does:
 
- Include any post in another with a simple shortcode that refers to the subarticle's post ID: writing [spid=123] in the article will include the post with ID #123. 
123's title will become an h2 in the authority article. Having multiple posts consolidated into one URL increases your chance of ranking for longtail searches.
- Adds a column to the "edit all posts" page, which column displays posts' IDs.
- Add a table of contents for the post Wikipedia style with another shortcode. [toc align=left] or [toc align=right]
- Adds a box to the post editor page, below the main post area, which allows you to redirect the post to any article on your site. 
Delete the text and save again to undo. The plugin doesn't assume you'll redirect because sometimes, as on Wikipedia, content is included in more than one post. 
So you might want to use it differently. 
- Removes redirected articles from the homepage, category archives and sitemaps. This reduces pagination because there are fewer posts to display, and redistributes
PageRank across fewer posts, so each is more likely to rank.
- Adds a subarticles page to the WP Dashboard so you can find all redirected articles in one convenient spot.
- Adds a settings page for CMC where you can request tech support, new features etc. There will be more settings added to it with planned features.

== Installation ==

1. Upload the content-marketing-cannon directory to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress

== Screenshots ==

1. How to include subarticles and a table of contents in an authority post.
2. Postids will appear in 'All Posts' page in dashboard.
3. Redirected subarticles appear here.

== Changelog ==

= 1.0 =
* First release