<?php
/**
 * Path: /ptwo/src/logout.php
 */
session_start();
session_unset();
session_destroy();

// Redirect back to the main index
header("Location: ../index.php");
exit;