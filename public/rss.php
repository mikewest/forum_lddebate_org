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
require($phpbb_root_path . 'memcache.' . $phpEx );

    include($phpbb_root_path . 'config.' . $phpEx);
    include($phpbb_root_path . 'includes/constants.' . $phpEx);

class fake {
    public $lang = 'en_gb';
    function optionget( $x ) { return FALSE; }
}

$user = new fake();
$user->theme = array( 'template_path' => 'LDDebate', 'bbcode_bitfield' => 'lNg=' );


function getRSS( $topic_id = NULL, $forum_id = NULL ) {
    global $dbms, $phpbb_root_path, $phpEx, $dbhost, $dbuser, $dbpasswd, $dbname, $dbport, $topic_id, $forum_id;
    include($phpbb_root_path . 'includes/db/' . $dbms . '.' . $phpEx);
    include($phpbb_root_path . 'includes/bbcode.' . $phpEx);
    include($phpbb_root_path . 'includes/functions_content.' . $phpEx);
    // Connect to DB
    $db                     = new $sql_db();
    $bbcode			        = new bbcode();
    $db->sql_connect($dbhost, $dbuser, $dbpasswd, $dbname, $dbport, false, defined('PHPBB_DB_NEW_LINK') ? PHPBB_DB_NEW_LINK : false);
    unset($dbpasswd);

    $start      = 0;


    $POSTS_TABLE    = POSTS_TABLE;
    $USER_TABLE     = USERS_TABLE;
    $TOPICS_TABLE   = TOPICS_TABLE;
    $FORUMS_TABLE   = FORUMS_TABLE;

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
                `p`.`bbcode_bitfield`,
                `p`.`bbcode_uid`,
                `p`.`enable_sig`,
                `u`.`username`,
                `u`.`user_sig`,
                `u`.`user_sig_bbcode_uid`,
                `u`.`user_sig_bbcode_bitfield`
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
            if ( empty( $title ) ) {
                $title = "“{$row[ 'post_subject' ]}” — forum.lddebate.org thread feed";
            }
            $bbcode->bbcode_second_pass( $row[ 'post_text' ],  $row[ 'bbcode_uid' ], $row[ 'bbcode_bitfield' ] );
            $bbcode->bbcode_second_pass( $row[ 'user_sig' ],  $row[ 'user_sig_bbcode_uid' ], $row[ 'user_sig_bbcode_bitfield' ] );
            $row[ 'post_text' ] = bbcode_nl2br( $row[ 'post_text' ] );
            $entries[] = array(
                'POST_ID'   =>  $row[ 'post_id' ],
                'POST_TIME' =>  strftime( '%Y-%m-%dT%H:%M:%SZ', $row[ 'post_time' ] ),
                'USERNAME'  =>  $row[ 'username' ],
                'USER_SIG'  =>  ( $row[ 'enable_sig' ] ? $row[ 'user_sig' ] : '' ),
                'SUBJECT'   =>  $row[ 'post_subject' ],
                'TEXT'      =>  str_replace(
                                    '{SMILIES_PATH}',
                                    'http://forum.lddebate.org/images/smilies',
                                    $row[ 'post_text' ] . '<hr />' . $row[ 'user_sig' ]
                                ),
                'URL'       =>  sprintf(
                                    'http://forum.lddebate.org/viewtopic.php?f=%1$d&amp;p=%2$d#p%2$d',
                                    $row[ 'forum_id' ], $row[ 'post_id' ]
                                )
            );
        }
        $db->sql_freeresult( $result );
    } elseif ( !empty( $forum_id ) ) {
        // Forum Feed

        // Retrieve the last 25 threads in the forum:
        $sql = <<<SQL
            SELECT
              `t`.`forum_id`,
              `f`.`forum_name`,
              `t`.`topic_title`,
              `t`.`topic_first_poster_name` as `topic_poster_username`,
              `t`.`topic_last_post_id`  AS `last_post_id`,
              `t`.`topic_last_post_time`  AS `last_post_time`,
              `t`.`topic_last_poster_name` as `last_poster_username`
            FROM
              `{$TOPICS_TABLE}` `t`
              JOIN
                `{$FORUMS_TABLE}` `f`
                ON `t`.`forum_id` = `f`.`forum_id`
            WHERE
              `t`.`forum_id` = {$forum_id}
            ORDER BY
              `t`.`topic_last_post_time` DESC
SQL;
        $result = $db->sql_query_limit( $sql, 25 );

        $title      = '';
        $maxtime    = 0;
        $url        = "http://forum.lddebate.org/viewtopic.php?f={$forum_id}";
        $rssurl     = "http://forum.lddebate.org/rss.php?f={$forum_id}";
        while ( $row = $db->sql_fetchrow( $result ) ) {
            $maxtime    = max( $maxtime, $row[ 'last_post_time' ] );
            $title      = "“{$row[ 'forum_name' ]}” — forum.lddebate.org forum feed";
            $entries[]  = array(
                'POST_ID'   =>  $row[ 'last_post_id' ],
                'POST_TIME' =>  strftime( '%Y-%m-%dT%H:%M:%SZ', $row[ 'last_post_time' ] ),
                'USERNAME'  =>  $row[ 'topic_poster_username' ],
                'SUBJECT'   =>  $row[ 'topic_title' ],
                'TEXT'      =>  'Last post: ' . strftime( '%Y-%m-%d at %H:%M GMT', $row[ 'last_post_time' ] ) . ' by ' . $row[ 'last_poster_username' ],
                'URL'       =>  sprintf(
                                    'http://forum.lddebate.org/viewtopic.php?f=%1$d&amp;p=%2$d#p%2$d',
                                    $row[ 'forum_id' ], $row[ 'last_post_id' ]
                                )

            );
        }
    }
    $xml        = '';
    if ( empty( $entries ) ) {
        $maxtime    = 0;
    } else {
        /* Print results */
        $isomaxtime = strftime( '%Y-%m-%dT%H:%M:%SZ', $maxtime );
        $xml .= <<<XML
        <feed xmlns="http://www.w3.org/2005/Atom">
            <title type="html">$title</title> 
            <link href="$url" />
            <link href="$rssurl" rel="self" />
            <updated>$isomaxtime</updated>
            <author> 
                <name>LDDebate.org</name>
            </author> 
            <id>$rssurl</id>
XML;

        foreach ( $entries as $entry ) {
            $xml .= <<<XML
            <entry>
                <title type="html">“{$entry[ 'SUBJECT' ]}” — {$entry[ 'USERNAME' ]}</title>
                <link href="{$entry[ 'URL' ]}"/>
                <id>{$entry[ 'URL' ]}</id>
                <updated>{$entry[ 'POST_TIME' ]}</updated>
                <content type="html"><![CDATA[{$entry[ 'TEXT' ]}]]></content>
            </entry>
XML;
        }
        $xml .= <<<XML
        </feed>
XML;
    }
    return array( 'maxtime' => $maxtime, 'content' => $xml );
}


// Initial var setup
$forum_id   = intval( ( empty( $_GET['f'] ) ? '' : $_GET['f'] ), 10 );
$topic_id   = intval( ( empty( $_GET['t'] ) ? '' : $_GET['t'] ), 10 );

if ( $topic_id || $forum_id ) {
    $data = clsMem::cache_or_get( "rss_topic_{$topic_id}_forum_{$forum_id}", 'getRSS', 1 );

    if ( $data[ 'maxtime' ] ) {
        header( 'Content-type: application/atom+xml; charset=utf-8' );
        header( 'Cache-Control: max-age=1800' );
        header( 'Last-Modified: ' . strftime( '%a, %d %b %Y %H:%M:%S GMT', $data[ 'maxtime' ] ) );
        print $data[ 'content' ];
    } else {
        header( 'HTTP/1.1 404 Not Found' );
    }
} else {
    header( 'HTTP/1.1 404 Not Found' );
}
