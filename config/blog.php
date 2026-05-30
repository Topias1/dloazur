<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Blog Source (D-06)
    |--------------------------------------------------------------------------
    |
    | Controls whether the public blog is served from flat Markdown files
    | or from the DB-backed Post model (Phase 6 default).
    |
    | Supported: "db", "files"
    |
    | Rollback: set BLOG_SOURCE=files in the environment to revert to the
    | file-backed path with zero code change.
    |
    */
    'source' => env('BLOG_SOURCE', 'db'),

];
