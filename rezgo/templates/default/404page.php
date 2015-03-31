<div class="container-fluid">
	<br>
  <div class="jumbotron">
    <h1 id="rezgo-404-head">Page not found <i class="fa fa-exclamation-triangle"></i></h1>
    <p class="lead">Sorry, we could not find the page you were looking for.</p>
    <p><a class="btn btn-lg btn-info" href="/" role="button">Return to home</a></p>
    <br />

    <span class="lead">Search the site</span>
    <form role="form" class="form-inline" onsubmit="top.location.href='<?=$site->base?>/keyword/'+$('#rezgo-404-search').val(); return false;">
      <div class="col-lg-6 row">
        <div class="input-group">
          <input class="form-control" type="text" name="search_for" id="rezgo-404-search" placeholder="what were you looking for?" value="<?=stripslashes(htmlentities($_REQUEST['search_for']))?>" />
          <span class="input-group-btn">
            <button class="btn btn-info" type="submit">Search</button>
          </span>
        </div>
      </div>         
    </form>
    
  </div>
  

</div>	

