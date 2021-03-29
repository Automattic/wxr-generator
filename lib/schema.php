<?php

/**
 * This schema defines all the fields for a valid WXR.
 */
return [
	'post' => [
		'container_tag' => 'item',
		'fields' => [
			'id' => [
				'type' => 'int',
				'tag' => 'wp:post_id'
			],
			'title' => [
				'type' => 'string',
				'tag' => 'title',
				'filter_hook' => 'the_title_rss'
			],
			'content' => [
				'type' => 'cdata',
				'tag'  => 'content:encoded'
			],
			'comments' => [
				'type' => 'comments'
			]
		]
	],
	'comment' => [
		'container_tag' => 'wp:comment',
		'fields' => [
			'content' => [
				'type' => 'cdata',
				'tag' => 'wp:comment_content'
			]
		]

	],
	'site_meta' => [
		'fields' => [
			'wxr_version' => [
				'type' => 'string',
				'element' => 'wp:wxr_version',
				'default' => '1.2'
			]
		]
	]
];
