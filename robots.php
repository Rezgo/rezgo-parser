<? if(strpos(getenv("DOCUMENT_ROOT"), 'dev') !== false) { ?>
	User-agent: *
	Disallow: /
<? } elseif(strpos(getenv("DOCUMENT_ROOT"), 'beta') !== false) { ?>
	User-agent: *
	Disallow: /
<? } else { ?>
	User-agent: *
	Allow: /
<? } ?>