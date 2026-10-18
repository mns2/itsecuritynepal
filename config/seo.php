<?php

return [
    /*
    | The one public domain search engines should index. Requests on this host always use
    | https://{primary_host} for canonicals, sitemap, robots.txt and structured data, and are indexable,
    | even if the server's .env still has staging values (APP_ENV=staging, APP_URL=https://new.…).
    | Any other host (staging, IP address, cPanel temp URL) stays noindex unless APP_ENV=production.
    */
    'primary_host' => env('SEO_PRIMARY_HOST', 'itsecuritynepal.com'),

    /* Force indexing on (true) or off (false) everywhere. Leave empty for the automatic rule above. */
    'indexing' => env('SEO_INDEXING'),

    /*
    | AI crawlers: true lets AI search and assistant crawlers (GPTBot, ClaudeBot, PerplexityBot, Google-Extended…)
    | read the public site like any other crawler; false blocks them in robots.txt. Search engines are unaffected.
    */
    'allow_ai_crawlers' => env('SEO_ALLOW_AI_CRAWLERS', true),
];
