<?php

// Vercel's PHP community runtime invokes this single serverless entrypoint.
// Laravel still owns routing through the normal public front controller.
require __DIR__.'/../public/index.php';
