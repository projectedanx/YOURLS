<?php

/**
 * Data for KSES: KsesAllowedProtocols
 */

return array(
        // Common
        'http://', 'https://', 'ftp://',
        'file://', 'smb://',
        'sftp://',
        'feed:', 'feed://',
        'mailto:',
        'news:', 'nntp://',

        // Old school bearded geek
        'gopher://', 'telnet://', 'finger://',
        'nntp://', 'worldwind://',

        // Dev
        'ssh://', 'svn://', 'svn+ssh://', 'git://', 'cvs://',
        'apt:',
        'market://', // Google Play
        'view-source:',

        // P2P
        'ed2k://', 'magnet:', 'udp://',

        // Streaming stuff
        'mms://', 'lastfm://', 'spotify:', 'rtsp://',

        // Text & voice
        'aim:', 'facetime://', 'gtalk:', 'xmpp:',
        'irc://', 'ircs://', 'mumble://',
        'callto:', 'skype:', 'sip:',
        'teamspeak://', 'tel:', 'ventrilo://', 'xfire:',
        'ymsgr:', 'tg://', 'whatsapp://',

        // Misc
        'steam:', 'steam://',
        'bitcoin:',
        'ldap://', 'ldaps://',

        // Purposedly removed for security
        /*
        'about:', 'chrome://', 'chrome-extension://',
        'javascript:',
        'data:',
        */
    );
