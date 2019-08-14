<?php if(strpos(getenv("DOCUMENT_ROOT"), 'dev') !== false) { ?>
	User-agent: *
	Disallow: /
<?php } elseif(strpos(getenv("DOCUMENT_ROOT"), 'beta') !== false) { ?>
	User-agent: *
	Disallow: /
<?php } else { ?>
	User-agent: *
	Allow: /
<?php } ?>