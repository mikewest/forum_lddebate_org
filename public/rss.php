<?php
/**
 *  RSS Feeds for everything, forums and topics.
 *
 *  @package    phpBB_rss
 *  @author     Mike West <mike@mikewest.org>
 */

/**
 * @ignore
 */
define('IN_PHPBB', true);
define('DEBUG', true);
define('DEBUG_EXTRA', true);
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : './';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
require($phpbb_root_path . 'config.' . $phpEx);
require($phpbb_root_path . 'includes/constants.' . $phpEx);
require($phpbb_root_path . 'includes/db/' . $dbms . '.' . $phpEx);
include($phpbb_root_path . 'includes/bbcode.' . $phpEx);

$db                     = new $sql_db();

// Connect to DB
$db->sql_connect($dbhost, $dbuser, $dbpasswd, $dbname, $dbport, false, defined('PHPBB_DB_NEW_LINK') ? PHPBB_DB_NEW_LINK : false);
unset($dbpasswd);




// Initial var setup
$forum_id   = intval( $_GET['f'], 10 );
$topic_id   = intval( $_GET['t'], 10 );
$start      = 0;


$POSTS_TABLE = POSTS_TABLE;
$USER_TABLE  = USERS_TABLE;

$entries    = array();

if ( !empty( $topic_id ) ) {
    // Topic feed.
    
    // Retrieve the last 25 posts in the thread.
    $sql = <<<SQL
        SELECT
            `p`.`post_id`,
            `p`.`forum_id`,
            `p`.`post_time`,
            `p`.`post_subject`,
            `p`.`post_text`,
            `p`.`enable_bbcode`,
            `p`.`enable_smilies`,
            `p`.`enable_magic_url`,
            `p`.`enable_sig`,
            `u`.`username`,
            `u`.`user_sig`
        FROM
            `$POSTS_TABLE` `p`
            JOIN `$USER_TABLE` `u`
                ON `p`.`poster_id` = `u`.`user_id`
        WHERE
            `topic_id`      = {$topic_id}   AND
            `post_approved` = 1
        ORDER BY
            `post_time` DESC
SQL;
    $result = $db->sql_query_limit( $sql, 25 );

    $title      = '';
    $maxtime    = 0;
    $url        = "http://forum.lddebate.org/viewtopic.php?t={$topic_id}";
    $rssurl     = "http://forum.lddebate.org/rss.php?t={$topic_id}";
    while ( $row = $db->sql_fetchrow( $result ) ) {
        $maxtime    =   max( $maxtime, $row[ 'post_time' ] );
        $title      =   empty( $title ) ? $row[ 'post_subject' ] : $title;
        $entries[] = array(
            'POST_ID'   =>  $row[ 'post_id' ],
            'POST_TIME' =>  strftime( '%Y-%m-%dT%H:%M:%SZ', $row[ 'post_time' ] ),
            'USERNAME'  =>  $row[ 'username' ],
            'USER_SIG'  =>  ( $row[ 'enable_sig' ] ? $row[ 'user_sig' ] : '' ),
            'SUBJECT'   =>  $row[ 'post_subject' ],
            'TEXT'      =>  $row[ 'post_text' ],
            'URL'       =>  sprintf(
                                'http://forum.lddebate.org/viewtopic.php?f=%1$d&p=%2$d#p%2$d',
                                $row[ 'forum_id' ], $row[ 'post_id' ]
                            )
        );
    }
    $db->sql_freeresult( $result );
}

/* Print results */
header( 'Content-type: text/xml' );
$title      = "&ldquo;$title&rdquo; &mdash; forum.lddebate.org";
$maxtime    = strftime( '%Y-%m-%dT%H:%M:%SZ', $maxtime );
print <<<XML
<feed xmlns="http://www.w3.org/2005/Atom">
    <title>$title</title> 
    <link href="$url" />
    <link href="$rssurl" rel="self" />
    <updated>$maxtime</updated>
    <author> 
        <name>LDDebate.org</name>
    </author> 
    <id><!-- RSS_URL --></id>
XML;

foreach ( $entries as $entry ) {
    print <<<XML
    <entry>
        <title type="html">&ldquo;{$entry[ 'SUBJECT' ]}&rdquo; &mdash; {$entry[ 'USERNAME' ]}</title>
        <link href="{$entry[ 'URL' ]}"/>
        <id>{$entry[ 'URL' ]}</id>
        <updated>{$entry[ 'POST_TIME' ]}</updated>
        <summary type="html">{$entry[ 'TEXT' ]}</summary>
    </entry>
XML;
}
print <<<XML
</feed>
XML;
