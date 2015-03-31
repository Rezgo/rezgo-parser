<?php

// index redirect to prevent file listing
header("Location: http://".$_SERVER['HTTP_HOST']);
exit;

?>