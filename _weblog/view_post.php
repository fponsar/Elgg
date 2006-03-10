<?php

	//	ELGG weblog view page

	// Run includes
		require("../includes.php");
		
		run("profile:init");
		run("friends:init");
		run("weblogs:init");
		
		define("context", "weblog");
		
		global $profile_id;
		global $individual;
		
		$individual = 1;
		
		if (isset($_REQUEST['post'])) {
			
			$post = (int) $_REQUEST['post'];
			
			$where = run("users:access_level_sql_where",$_SESSION['userid']);
			$post = db_query("select * from weblog_posts where ($where) and ident = $post");
			
			if (sizeof($post) > 0) {
				$post = $post[0];
			} else {
				$post = "";
				$post->weblog = -1;
				$post->owner = -1;
				$post->title = gettext("Access denied or post not found");
				$post->posted = time();
				$post->ident = -1;
				$post->body = gettext("Either this blog post doesn't exist or you don't currently have access privileges to view it.");
			}
			
			global $page_owner;
			global $profile_id;
			$profile_id = $post->weblog;
			$page_owner = $post->weblog;
			
			$title = run("profile:display:name") . " :: " . gettext("Weblog") . " :: " . stripslashes($post->title);
			
			$time = gmdate("F d, Y",$post->posted);
			$body = "<h2 class=\"weblog_dateheader\">$time</h2>\n";
			
			$body .= run("weblogs:posts:view:individual",$post);
			
			$body = run("templates:draw", array(
							'context' => 'contentholder',
							'title' => $title,
							'body' => $body
						)
						);
			
			echo run("templates:draw:page", array(
							$title, $body
						)
						);

		}

?>